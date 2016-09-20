<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\SiteUrls;

/**
 * @see plugins/Referrers/functions.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/Referrers/functions.php';

/**
 */
class Referrers extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'Insights.addReportToOverview'      => 'addReportToInsightsOverview',
            'Live.getAllVisitorDetails'         => 'extendVisitorDetails',
            'Request.getRenamedModuleAndAction' => 'renameDeprecatedModuleAndAction',
            'Tracker.setTrackerCacheGeneral'    => 'setTrackerCacheGeneral'
        );
    }

    public function setTrackerCacheGeneral(&$cacheContent)
    {
        $siteUrls = new SiteUrls();
        $urls = $siteUrls->getAllCachedSiteUrls();

        return $cacheContent['allUrlsByHostAndIdSite'] = $siteUrls->groupUrlsByHost($urls);
    }

    public function renameDeprecatedModuleAndAction(&$module, &$action)
    {
        if($module == 'Referers') {
            $module = 'Referrers';
        }
    }

    public function extendVisitorDetails(&$visitor, $details)
    {
        $instance = new Visitor($details);

        $visitor['referrerType']             = $instance->getReferrerType();
        $visitor['referrerTypeName']         = $instance->getReferrerTypeName();
        $visitor['referrerName']             = $instance->getReferrerName();
        $visitor['referrerKeyword']          = $instance->getKeyword();
        $visitor['referrerKeywordPosition']  = $instance->getKeywordPosition();
        $visitor['referrerUrl']              = $instance->getReferrerUrl();
        $visitor['referrerSearchEngineUrl']  = $instance->getSearchEngineUrl();
        $visitor['referrerSearchEngineIcon'] = $instance->getSearchEngineIcon();
    }

    public function addReportToInsightsOverview(&$reports)
    {
        $reports['Referrers_getWebsites']  = array();
        $reports['Referrers_getCampaigns'] = array();
        $reports['Referrers_getSocials']   = array();
        $reports['Referrers_getSearchEngines'] = array();
    }

    /**
     * DataTable filter callback that returns the HTML prefix for a label in the
     * 'getAll' report based on the row's referrer type.
     *
     * @param int $referrerType The referrer type.
     * @return string
     */
    public function setGetAllHtmlPrefix($referrerType)
    {
        // get singular label for referrer type
        $indexTranslation = '';
        switch ($referrerType) {
            case Common::REFERRER_TYPE_DIRECT_ENTRY:
                $indexTranslation = 'Referrers_DirectEntry';
                break;
            case Common::REFERRER_TYPE_ORGANIC_SEARCH:
                $indexTranslation = 'Referrers_ColumnOrganicSearch';
                break;
            case Common::REFERRER_TYPE_SOCIAL:
                $indexTranslation = 'Referrers_ColumnSocial';
                break;
            case Common::REFERRER_TYPE_PAID_SEARCH:
                $indexTranslation = 'General_ColumnPaidSearch';
                break;
            case Common::REFERRER_TYPE_REFERRAL:
                $indexTranslation = 'Referrers_ColumnReferral';
                break;
            case Common::REFERRER_TYPE_OTHERS:
                $indexTranslation = 'Referrers_ColumnOthers';
                break;
            case Common::REFERRER_TYPE_DIGITAL:
                $indexTranslation = 'Referrers_ColumnDigital';
                break;
            case Common::REFERRER_TYPE_ECRM:
                $indexTranslation = 'Referrers_ColumnECRM';
                break;
            case Common::REFERRER_TYPE_AFFILIATE:
                $indexTranslation = 'Referrers_ColumnAffiliate';
                break;
            default:
                // case of newsletter, partners, before Piwik 0.2.25
                $indexTranslation = 'General_Others';
                break;
        }

        $label = strtolower(Piwik::translate($indexTranslation));

        // return html that displays it as grey & italic
        return '<span class="datatable-label-category"><em>(' . $label . ')</em></span>';
    }
}
