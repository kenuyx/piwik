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
use Piwik\DataTable;
use Piwik\Piwik;

/**
 * API for plugin Ecommerce
 *
 * @method static \Piwik\Plugins\Ecommerce\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    public function getEcommerceOrders($idSite, $period, $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $table = $this->loadConversionsFromDatabase($idSite, $period, $date, $segment, $offset = 0, $filter_limit = false, $minTimestamp = false, $filterSortOrder = false);
        return $table;
    }

    private function loadConversionsFromDatabase($idSite, $period, $date, $segment = false, $offset = 0, $limit = 100, $minTimestamp = false, $filterSortOrder = false, $visitorId = false)
    {
        $model = new Model();
        $data = $model->queryConversions($idSite, $period, $date, $segment, $offset, $limit, $visitorId, $minTimestamp, $filterSortOrder);
        return $this->makeConversionTableFromArray($data);
    }

    /**
     * @param $data
     * @return DataTable
     * @throws Exception
     */
    private function makeConversionTableFromArray($data)
    {
        $dataTable = new DataTable();
        $dataTable->addRowsFromSimpleArray($data);

//        if (!empty($data[0])) {
//            $columnsToNotAggregate = array_map(function () {
//                return 'skip';
//            }, $data[0]);
//
//            $dataTable->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, $columnsToNotAggregate);
//        }


        return $dataTable;
    }
}
