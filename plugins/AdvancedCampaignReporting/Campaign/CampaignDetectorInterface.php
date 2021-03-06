<?php
/**
 * Piwik PRO - cloud hosting and enterprise analytics consultancy
 * from the creators of Piwik.org
 *
 * @link http://piwik.pro
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\AdvancedCampaignReporting\Campaign;

use Piwik\Tracker\Request;

interface CampaignDetectorInterface
{
    /**
     * @param Request $request
     * @param $campaignParameters
     * @return array|bool
     */
    public function detectCampaignFromRequest(Request $request, $campaignParameters);

    /**
     * @param $queryString
     * @param $campaignParameters
     * @return array|bool
     */
    public function detectCampaignFromString($queryString, $campaignParameters);

    /**
     * @param $visitorInfo
     * @param $campaignParameters
     * @return array|bool
     */
    public function detectCampaignFromVisit($visitorInfo, $campaignParameters);
}
