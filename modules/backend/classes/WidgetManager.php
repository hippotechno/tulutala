<?php namespace Backend\Classes;

use Str;
use BackendAuth;
use SystemException;
use System\Classes\PluginManager;
use Event;

/**
 * Widget manager
 *
 * @package winter\wn-backend-module
 * @author Alexey Bobkov, Samuel Georges
 */
class WidgetManager
{
    use \Winter\Storm\Support\Traits\Singleton;

    /**
     * @var array An array of form widgets. Stored in the form of ['FormWidgetClass' => $formWidgetInfo].
     */
    protected $formWidgets;

    /**
     * @var array Cache of form widget registration callbacks.
     */
    protected $formWidgetCallbacks = [];

    /**
     * @var array An array of form widgets keyed by their code. Stored in the form of ['formwidgetcode' => 'FormWidgetClass'].
     */
    protected $formWidgetHints;

    /**
     * @var array An array of filter widgets. Stored in the form of ['FilterWidgetClass' => $filterWidgetInfo].
     */
    protected $filterWidgets;

    /**
     * @var array Cache of filter widget registration callbacks.
     */
    protected $filterWidgetCallbacks = [];

    /**
     * @var array An array of filter widgets keyed by their code. Stored in the form of ['filterwidgetcode' => 'FilterWidgetClass'].
     */
    protected $filterWidgetHints;

    /**
     * @var array An array of report widgets.
     */
    protected $reportWidgets;

    /**
     * @var array Cache of report widget registration callbacks.
     */
    protected $reportWidgetCallbacks = [];

    /**
     * @var System\Classes\PluginManager
     */
    protected $pluginManager;

    /**
     * Initialize this singleton.
     */
    protected function init()
    {
        $this->pluginManager = PluginManager::instance();
    }

    //
    // Form Widgets
    //

    /**
     * Returns a list of registered form widgets.
     * @return array Array keys are class names.
     */
    public function listFormWidgets()
    {
        if ($this->formWidgets === null) {
            $this->formWidgets = [];

            /*
             * Load module widgets
             */
            foreach ($this->formWidgetCallbacks as $callback) {
                $callback($this);
            }

            /*
             * Load plugin widgets
             */
            $plugins = $this->pluginManager->getPlugins();

            foreach ($plugins as $plugin) {
                if (!is_array($widgets = $plugin->registerFormWidgets())) {
                    continue;
                }

                foreach ($widgets as $className => $widgetInfo) {
                    $this->registerFormWidget($className, $widgetInfo);
                }
            }
        }

        return $this->formWidgets;
    }

    /**
     * Registers a single form widget.
     * @param string $className Widget class name.
     * @param array $widgetInfo Registration information, can contain a `code` key.
     * @return void
     */
    public function registerFormWidget($className, $widgetInfo = null)
    {
        if (!is_array($widgetInfo)) {
            $widgetInfo = ['code' => $widgetInfo];
        }

        $widgetCode = $widgetInfo['code'] ?? null;

        if (!$widgetCode) {
            $widgetCode = Str::getClassId($className);
        }

        $this->formWidgets[$className] = $widgetInfo;
        $this->formWidgetHints[$widgetCode] = $className;
    }

    /**
     * Manually registers form widget for consideration. Usage:
     *
     *     WidgetManager::registerFormWidgets(function ($manager) {
     *         $manager->registerFormWidget('Backend\FormWidgets\CodeEditor', 'codeeditor');
     *     });
     *
     */
    public function registerFormWidgets(callable $definitions)
    {
        $this->formWidgetCallbacks[] = $definitions;
    }

    /**
     * Returns a class name from a form widget code
     * Normalizes a class name or converts an code to its class name.
     * @param string $name Class name or form widget code.
     * @return string The class name resolved, or the original name.
     */
    public function resolveFormWidget($name)
    {
        if ($this->formWidgets === null) {
            $this->listFormWidgets();
        }

        $hints = $this->formWidgetHints;

        if (isset($hints[$name])) {
            return $hints[$name];
        }

        $_name = Str::normalizeClassName($name);
        if (isset($this->formWidgets[$_name])) {
            return $_name;
        }

        return $name;
    }

    //
    // Filter Widgets
    //

    /**
     * Returns a list of registered filter widgets.
     * @return array Array keys are class names.
     */
    public function listFilterWidgets()
    {
        if ($this->filterWidgets === null) {
            $this->filterWidgets = [];

            /*
             * Load module widgets
             */
            foreach ($this->filterWidgetCallbacks as $callback) {
                $callback($this);
            }

            /*
             * Load plugin widgets
             */
            $plugins = $this->pluginManager->getPlugins();

            foreach ($plugins as $plugin) {
                if (!is_array($widgets = $plugin->registerFilterWidgets())) {
                    continue;
                }

                foreach ($widgets as $className => $widgetInfo) {
                    $this->registerFilterWidget($className, $widgetInfo);
                }
            }
        }

        return $this->filterWidgets;
    }

    /**
     * Registers a single filter widget.
     * @param string $className Widget class name.
     * @param array|string|null $widgetInfo Registration information, can contain a `code` key.
     * @return void
     */
    public function registerFilterWidget($className, $widgetInfo = null)
    {
        if (!is_array($widgetInfo)) {
            $widgetInfo = ['code' => $widgetInfo];
        }

        $widgetCode = $widgetInfo['code'] ?? null;

        if (!$widgetCode) {
            $widgetCode = Str::getClassId($className);
        }

        $this->filterWidgets[$className] = $widgetInfo;
        $this->filterWidgetHints[$widgetCode] = $className;
    }

    /**
     * Manually registers filter widgets for consideration. Usage:
     *
     *     WidgetManager::registerFilterWidgets(function ($manager) {
     *         $manager->registerFilterWidget('Backend\FilterWidgets\Text', 'text');
     *     });
     *
     */
    public function registerFilterWidgets(callable $definitions)
    {
        $this->filterWidgetCallbacks[] = $definitions;
    }

    /**
     * Returns a class name from a filter widget code.
     * @param string $name Class name or filter widget code.
     * @return string The class name resolved, or the original name.
     */
    public function resolveFilterWidget($name)
    {
        if ($this->filterWidgets === null) {
            $this->listFilterWidgets();
        }

        $hints = $this->filterWidgetHints;

        if (isset($hints[$name])) {
            return $hints[$name];
        }

        $_name = Str::normalizeClassName($name);
        if (isset($this->filterWidgets[$_name])) {
            return $_name;
        }

        return $name;
    }

    //
    // Report Widgets
    //

    /**
     * Returns a list of registered report widgets.
     * @return array Array keys are class names.
     */
    public function listReportWidgets()
    {
        if ($this->reportWidgets === null) {
            $this->reportWidgets = [];

            /*
             * Load module widgets
             */
            foreach ($this->reportWidgetCallbacks as $callback) {
                $callback($this);
            }

            /*
             * Load plugin widgets
             */
            $plugins = $this->pluginManager->getPlugins();

            foreach ($plugins as $plugin) {
                if (!is_array($widgets = $plugin->registerReportWidgets())) {
                    continue;
                }

                foreach ($widgets as $className => $widgetInfo) {
                    $this->registerReportWidget($className, $widgetInfo);
                }
            }
        }

        /**
         * @event system.reportwidgets.extendItems
         * Enables adding or removing report widgets.
         *
         * You will have access to the WidgetManager instance and be able to call the appropiate methods
         * $manager->registerReportWidget();
         * $manager->removeReportWidget();
         *
         * Example usage:
         *
         *     Event::listen('system.reportwidgets.extendItems', function ($manager) {
         *          $manager->removeReportWidget('Acme\ReportWidgets\YourWidget');
         *     });
         *
         */
        Event::fire('system.reportwidgets.extendItems', [$this]);

        $user = BackendAuth::getUser();
        foreach ($this->reportWidgets as $widget => $config) {
            if (!empty($config['permissions'])) {
                if (!$user->hasAccess($config['permissions'], false)) {
                    unset($this->reportWidgets[$widget]);
                }
            }
        }

        return $this->reportWidgets;
    }

    /**
     * Returns the raw array of registered report widgets.
     * @return array Array keys are class names.
     */
    public function getReportWidgets()
    {
        return $this->reportWidgets;
    }

    /*
     * Registers a single report widget.
     */
    public function registerReportWidget($className, $widgetInfo)
    {
        $this->reportWidgets[$className] = $widgetInfo;
    }

    /**
     * Manually registers report widget for consideration. Usage:
     *
     *     WidgetManager::registerReportWidgets(function ($manager) {
     *         $manager->registerReportWidget('Winter\GoogleAnalytics\ReportWidgets\TrafficOverview', [
     *             'name' => 'Google Analytics traffic overview',
     *             'context' => 'dashboard'
     *         ]);
     *     });
     *
     */
    public function registerReportWidgets(callable $definitions)
    {
        $this->reportWidgetCallbacks[] = $definitions;
    }

    /**
     * Remove a registered ReportWidget.
     * @param string $className Widget class name.
     * @return void
     */
    public function removeReportWidget($className)
    {
        if (!$this->reportWidgets) {
            throw new SystemException('Unable to remove a widget before widgets are loaded.');
        }

        unset($this->reportWidgets[$className]);
    }
}
