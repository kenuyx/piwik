<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\AdvancedCampaignReporting;

use Piwik\Piwik;

class Visitor
{
    private $details = array();

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function getCampaignName()
    {
        if (isset($this->details['campaign_name'])) {
            return $this->details['campaign_name'];
        }
        return Piwik::translate('General_Unknown');
    }

    public function getCampaignKeyword()
    {
        if (isset($this->details['campaign_keyword'])) {
            return $this->details['campaign_keyword'];
        }
        return Piwik::translate('General_Unknown');
    }

    public function getCampaignSource()
    {
        if (isset($this->details['campaign_source'])) {
            return $this->details['campaign_source'];
        }
        return Piwik::translate('General_Unknown');
    }

    public function getCampaignMedium()
    {
        if (isset($this->details['campaign_medium'])) {
            return $this->details['campaign_medium'];
        }
        return Piwik::translate('General_Unknown');
    }

    public function getCampaignContent()
    {
        if (isset($this->details['campaign_content'])) {
            return $this->details['campaign_content'];
        }
        return Piwik::translate('General_Unknown');
    }

}