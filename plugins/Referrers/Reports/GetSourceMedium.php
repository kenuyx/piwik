<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Plugins\Referrers\Columns\Referrer;
use Piwik\Plugins\Referrers\Referrers;

class GetSourceMedium extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new Referrer();
        $this->name          = Piwik::translate('Referrers_WidgetGetAll');
        $this->documentation = Piwik::translate('Referrers_AllReferrersReportDocumentation', '<br />');
        $this->order = 2;
        $this->widgetTitle  = 'Referrers_WidgetGetAll';
    }

    public function getDefaultTypeViewDataTable()
    {
        return HtmlTable\AllColumns::ID;
    }

    public function configureView(ViewDataTable $view)
    {
        $referrers = new Referrers();
        $setGetAllHtmlPrefix = array($referrers, 'setGetAllHtmlPrefix');

        $view->config->columns_to_display = array(
            'label',
            'nb_visits',
            //'nb_visits_returning',
            'nb_uniq_visitors',
            //'nb_uniq_visitors_returning',

            'nb_users',             // existing
            'nb_actions',           // existing
            'nb_actions_per_visit', // existing
            'avg_time_on_site',     // existing

            'bounce_rate',
            //'nb_pageviews',
            //'avg_time_on_site'
        );
        $view->config->show_exclude_low_population = false;
        $view->config->show_goals = true;
        $view->config->addTranslation('label', $this->dimension->getName());

        $view->requestConfig->filter_limit = 20;

        if ($view->isViewDataTableId(HtmlTable::ID)) {
            $view->config->disable_row_actions = true;
        }

        $view->config->filters[] = array('MetadataCallbackAddMetadata', array('referer_type', 'html_label_prefix', $setGetAllHtmlPrefix));
    }

}
