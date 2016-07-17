<?php
/**
 * Piwik PRO - cloud hosting and enterprise analytics consultancy
 * from the creators of Piwik.org
 *
 * @link http://piwik.pro
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\AdvancedCampaignReporting;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Translation\Translator;
use Piwik\View;

/**
 *
 * @package AdvancedCampaignReporting
 */
class Controller extends \Piwik\Plugin\Controller
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;

        parent::__construct();
    }

    public function indexCampaigns()
    {
        $view = new View('@AdvancedCampaignReporting/index');
        $view->name = $this->getName(true);
        $view->source = $this->getSource(true);
        $view->medium = $this->getMedium(true);
        $view->keyword = $this->getKeyword(true);
        $view->content = $this->getContent(true);
        $view->combinedSourceMedium = $this->getSourceMedium(true);
        return $view->render();
    }

    public function combineSourceMedium()
    {
        $view = new View('@AdvancedCampaignReporting/sourceMedium');
        $this->setPeriodVariablesView($view);
        $view->graphEvolutionSourceMedium = $this->getSourceMediumEvolutionGraph(array(), array('nb_visits'), 'getIndexGraph');
        $view->combinedSourceMedium = $this->getSourceMedium();
        return $view->render();
    }

    public function getSourceMediumEvolutionGraph(array $columns = array(), array $defaultColumns = array(), $callingAction = __FUNCTION__)
    {
        if (empty($columns)) {
            $columns = Common::getRequestVar('columns', false);
            if (false !== $columns) {
                $columns = Piwik::getArrayFromApiParameter($columns);
            }
        }

        $documentation = $this->translator->translate('VisitsSummary_VisitsSummaryDocumentation') . '<br />'
            . $this->translator->translate('General_BrokenDownReportDocumentation') . '<br /><br />'

            . '<b>' . $this->translator->translate('General_ColumnNbVisits') . ':</b> '
            . $this->translator->translate('General_ColumnNbVisitsDocumentation') . '<br />'

            . '<b>' . $this->translator->translate('General_ColumnNbUniqVisitors') . ':</b> '
            . $this->translator->translate('General_ColumnNbUniqVisitorsDocumentation') . '<br />'

            . '<b>' . $this->translator->translate('General_ColumnNbActions') . ':</b> '
            . $this->translator->translate('General_ColumnNbActionsDocumentation') . '<br />'

            . '<b>' . $this->translator->translate('General_ColumnNbUsers') . ':</b> '
            . $this->translator->translate('General_ColumnNbUsersDocumentation') . ' (<a rel="noreferrer"  target="_blank" href="http://piwik.org/docs/user-id/">User ID</a>)<br />'

            . '<b>' . $this->translator->translate('General_ColumnActionsPerVisit') . ':</b> '
            . $this->translator->translate('General_ColumnActionsPerVisitDocumentation');

        $selectableColumns = array(
            'nb_visits',
            'nb_visits_returning',//'nb_visits_new_rate',       // (nb_visits - nb_visits_returning) / nb_visits
            'nb_uniq_visitors',
            'nb_uniq_visitors_returning',//'nb_uniq_visitors_new',     // nb_visits_new = nb_uniq_visitors - nb_uniq_visitors_returning
            'bounce_rate',
            'nb_pageviews',//'nb_pageviews_per_visit',   // nb_pageviews / nb_visits
            'avg_time_on_site',
            'conversion_rate',
            'nb_conversions',
            'revenue'
        );

        // $callingAction may be specified to distinguish between
        // "VisitsSummary_WidgetLastVisits" and "VisitsSummary_WidgetOverviewGraph"
        $view = $this->getLastUnitGraphAcrossPlugins($this->pluginName, $callingAction, $columns,
            $selectableColumns, $documentation);

        if (empty($view->config->columns_to_display) && !empty($defaultColumns)) {
            $view->config->columns_to_display = $defaultColumns;
        }

        return $this->renderView($view);
    }

    public function getKeywordContentFromNameId()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getNameFromSourceMediumId()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getName()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getSource()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getMedium()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getKeyword()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getContent()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getSourceMedium()
    {
        return $this->renderReport(__FUNCTION__);
    }
}
