<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Ecommerce;

use Exception;
use Piwik\Common;
use Piwik\DataAccess\LogAggregator;
use Piwik\Date;
use Piwik\Db;
use Piwik\Period;
use Piwik\Period\Range;
use Piwik\Piwik;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Tracker\GoalManager;

class Model
{

    /**
     * @param $idSite
     * @param $period
     * @param $date
     * @param $segment
     * @param $limit
     * @param $visitorId
     * @param $minTimestamp
     * @param $filterSortOrder
     * @return array
     * @throws Exception
     */
    public function queryConversions($idSite, $period, $date, $segment, $offset, $limit, $visitorId, $minTimestamp, $filterSortOrder)
    {
        list($sql, $bind) = $this->makeConversionsQueryString($idSite, $period, $date, $segment, $offset, $limit, $visitorId, $minTimestamp, $filterSortOrder);
        return Db::fetchAll($sql, $bind);
    }

    /**
     * @param $idSite
     * @param $period
     * @param $date
     * @param $segment
     * @param int $offset
     * @param int $limit
     * @param $visitorId
     * @param $minTimestamp
     * @param $filterSortOrder
     * @return array
     * @throws Exception
     */
    private function makeConversionsQueryString($idSite, $period, $date, $segment, $offset, $limit, $visitorId, $minTimestamp, $filterSortOrder)
    {
        // If no other filter, only look at the last 24 hours of stats
        if (empty($visitorId)
            && empty($limit)
            && empty($offset)
            && empty($period)
            && empty($date)
        ) {
            $period = 'day';
            $date = 'yesterdaySameTime';
        }

        list($whereClause, $bindIdSites) = $this->getIdSitesWhereClause($idSite);

        list($whereBind, $where) = $this->getWhereClauseAndBind($whereClause, $bindIdSites, $idSite, $period, $date, $visitorId, $minTimestamp);

        if (strtolower($filterSortOrder) == 'desc') {
            $filterSortOrder = 'DESC';
        } else {
            $filterSortOrder = 'ASC';
        }

        $segment = new Segment($segment, $idSite);

        $select = "
            log_conversion.idorder as label,
            log_conversion.campaign_source as source,
            log_conversion.campaign_medium as medium,
            " . LogAggregator::getSqlRevenue('revenue') . " as revenue,
            " . LogAggregator::getSqlRevenue('revenue_shipping') . " as shipping,
            " . LogAggregator::getSqlRevenue('revenue_subtotal') . " as subtotal,
            " . LogAggregator::getSqlRevenue('revenue_discount') . " as discount,
            log_conversion.custom_var_v1 as mobile";
        $from = "log_conversion";
        $groupBy = false;
        $limit = $limit >= 1 ? (int)$limit : 0;
        $offset = $offset >= 1 ? (int)$offset : 0;

        $orderBy = '';
        if (count($bindIdSites) <= 1) {
            $orderBy = 'log_conversion.idsite, ';
        }

        $orderBy .= "log_conversion.server_time " . $filterSortOrder;

        $query = $segment->getSelectQuery($select, $from, $where, $whereBind, $orderBy, $groupBy, $limit, $offset);

        $query['sql'] = str_replace("log_visit.campaign", "log_conversion.campaign", $query['sql']);

        return array($query['sql'], $query['bind']);
    }

    /**
     * @param $idSite
     * @param string $table
     * @return array
     */
    private function getIdSitesWhereClause($idSite, $table = 'log_conversion')
    {
        $idSites = array($idSite);
        $idSitesBind = Common::getSqlStringFieldsArray($idSites);
        $whereClause = $table . ".idsite in ($idSitesBind) ";
        return array($whereClause, $idSites);
    }

    /**
     * @param string $whereClause
     * @param array $bindIdSites
     * @param $idSite
     * @param $period
     * @param $date
     * @param $visitorId
     * @param $minTimestamp
     * @return array
     * @throws Exception
     */
    private function getWhereClauseAndBind($whereClause, $bindIdSites, $idSite, $period, $date, $visitorId, $minTimestamp)
    {
        $where = array();
        $where[] = $whereClause;
        $whereBind = $bindIdSites;

        if (!empty($visitorId)) {
            $where[] = "log_conversion.idvisitor = ? ";
            $whereBind[] = @Common::hex2bin($visitorId);
        }

        if (!empty($minTimestamp)) {
            $where[] = "log_conversion.server_time > ? ";
            $whereBind[] = date("Y-m-d H:i:s", $minTimestamp);
        }

        // SQL Filter with provided period
        if (!empty($period) && !empty($date)) {
            $currentSite = $this->makeSite($idSite);
            $currentTimezone = $currentSite->getTimezone();

            $dateString = $date;
            if ($period == 'range') {
                $processedPeriod = new Range('range', $date);
                if ($parsedDate = Range::parseDateRange($date)) {
                    $dateString = $parsedDate[2];
                }
            } else {
                $processedDate = Date::factory($date);
                if ($date == 'today'
                    || $date == 'now'
                    || $processedDate->toString() == Date::factory('now', $currentTimezone)->toString()
                ) {
                    $processedDate = $processedDate->subDay(1);
                }
                $processedPeriod = Period\Factory::build($period, $processedDate);
            }
            $dateStart = $processedPeriod->getDateStart()->setTimezone($currentTimezone);
            $where[] = "log_conversion.server_time >= ?";
            $whereBind[] = $dateStart->toString('Y-m-d H:i:s');
            $where[] = "log_conversion.idgoal = ?";
            $whereBind[] = GoalManager::IDGOAL_ORDER;

            if (!in_array($date, array('now', 'today', 'yesterdaySameTime'))
                && strpos($date, 'last') === false
                && strpos($date, 'previous') === false
                && Date::factory($dateString)->toString('Y-m-d') != Date::factory('now', $currentTimezone)->toString()
            ) {
                $dateEnd = $processedPeriod->getDateEnd()->setTimezone($currentTimezone);
                $where[] = " log_conversion.server_time <= ?";
                $dateEndString = $dateEnd->addDay(1)->toString('Y-m-d H:i:s');
                $whereBind[] = $dateEndString;
            }
        }

        if (count($where) > 0) {
            $where = join("
				AND ", $where);
        } else {
            $where = false;
        }
        return array($whereBind, $where);
    }

    /**
     * @param $idSite
     * @return Site
     */
    private function makeSite($idSite)
    {
        return new Site($idSite);
    }
} 
