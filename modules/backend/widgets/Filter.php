<?php

namespace Backend\Widgets;

use Backend\Classes\FilterScope;
use Backend\Classes\FilterWidgetBase;
use Backend\Classes\WidgetBase;
use Backend\Classes\WidgetManager;
use Backend\Facades\BackendAuth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Lang;
use Winter\Storm\Exception\ApplicationException;
use Winter\Storm\Support\Facades\DB;
use Winter\Storm\Support\Facades\DbDongle;
use Winter\Storm\Support\Str;

/**
 * Filter Widget
 * Renders a container used for filtering things.
 *
 * @package winter\wn-backend-module
 * @author Alexey Bobkov, Samuel Georges
 */
class Filter extends WidgetBase
{
    //
    // Configurable properties
    //

    /**
     * @var array Scope definition configuration.
     */
    public $scopes;

    /**
     * @var string The context of this filter, scopes that do not belong
     * to this context will not be shown.
     */
    public $context;

    /**
     * @var \Winter\Storm\Database\Model|null Model object used by this filter.
     */
    public $model;

    /**
     * @var \Backend\Widgets\Lists|null List widget this filter is attached to.
     */
    public $listWidget;

    //
    // Object properties
    //

    /**
     * @inheritDoc
     */
    protected $defaultAlias = 'filter';

    /**
     * @var boolean Determines if scope definitions have been created.
     */
    protected $scopesDefined = false;

    /**
     * @var array Collection of all scopes used in this filter.
     */
    protected $allScopes = [];

    /**
     * @var array Collection of all scopes models used in this filter.
     */
    protected $scopeModels = [];

    /**
     * @var array Collection of custom filter widgets keyed by scope name.
     */
    protected $filterWidgets = [];

    /**
     * @var array List of CSS classes to apply to the filter container element
     */
    public $cssClasses = [];

    /**
     * Initialize the widget, called by the constructor and free from its parameters.
     */
    public function init()
    {
        $this->fillFromConfig([
            'scopes',
            'context',
            'model',
            'listWidget',
        ]);
    }

    /**
     * Renders the widget.
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('filter');
    }

    /**
     * Prepares the view data
     */
    public function prepareVars()
    {
        $this->defineFilterScopes();
        $this->vars['cssClasses'] = implode(' ', $this->cssClasses);
        $this->vars['scopes'] = $this->allScopes;
    }

    /**
     * Renders the HTML element for a scope
     */
    public function renderScopeElement($scope)
    {
        if ($this->isFilterWidget($scope)) {
            return $this->makeScopeFilterWidget($scope)->render();
        }

        throw new ApplicationException(sprintf(
            "The filter scope '%s' uses type '%s', but no filter widget is registered for that type.",
            $scope->scopeName,
            $scope->type
        ));
    }

    /**
     * Returns a HTML encoded value containing the other scopes this scope depends on
     * @param  \Backend\Classes\FilterScope $scope
     * @return string
     */
    public function getScopeDepends($scope)
    {
        if (!$scope->dependsOn) {
            return '';
        }

        $dependsOn = is_array($scope->dependsOn) ? $scope->dependsOn : [$scope->dependsOn];
        $dependsOn = htmlspecialchars(json_encode($dependsOn), ENT_QUOTES, 'UTF-8');
        return $dependsOn;
    }

    //
    // AJAX
    //

    /**
     * Update a filter scope value.
     * @return array
     */
    public function onFilterUpdate()
    {
        $this->defineFilterScopes();

        if (!$scope = post('scopeName')) {
            return;
        }

        $scope = $this->getScope($scope);
        $scopeFilterWidget = null;

        if ($this->isFilterWidget($scope)) {
            $scopeFilterWidget = $this->makeScopeFilterWidget($scope);
            $this->setScopeValue($scope, $scopeFilterWidget->getActiveValue());
        }
        else {
            switch ($scope->type) {
                case 'group':
                    $data = json_decode(post('options'), true);
                    $active = $this->optionsFromAjax($data ?: null);
                    $this->setScopeValue($scope, $active);
                    break;

                case 'button-group':
                case 'dropdown':
                    $this->setScopeValue($scope, post('value') ?: null);
                    break;

                case 'checkbox':
                    $checked = post('value') == 'true';
                    $this->setScopeValue($scope, $checked);
                    break;

                case 'switch':
                    $value = post('value');
                    $this->setScopeValue($scope, $value);
                    break;

                case 'date':
                    $data = json_decode(post('options'), true);
                    $dates = $this->datesFromAjax($data['dates'] ?? null);

                    if (!empty($dates)) {
                        list($date) = $dates;
                    }
                    else {
                        $date = null;
                    }

                    $this->setScopeValue($scope, $date);
                    break;

                case 'daterange':
                    $data = json_decode(post('options'), true);
                    $dates = $this->datesFromAjax($data['dates'] ?? null);

                    if (!empty($dates)) {
                        list($after, $before) = $dates;

                        $dates = [$after, $before];
                    }
                    else {
                        $dates = null;
                    }

                    $this->setScopeValue($scope, $dates);
                    break;

                case 'number':
                    $data = json_decode(post('options'), true);
                    $numbers = $this->numbersFromAjax($data['numbers'] ?? null);

                    if (!empty($numbers)) {
                        list($number) = $numbers;
                    }
                    else {
                        $number = null;
                    }

                    $this->setScopeValue($scope, $number);
                    break;

                case 'numberrange':
                    $data = json_decode(post('options'), true);
                    $numbers = $this->numbersFromAjax($data['numbers'] ?? null);

                    if (!empty($numbers)) {
                        list($min, $max) = $numbers;

                        $numbers = [$min, $max];
                    }
                    else {
                        $numbers = null;
                    }

                    $this->setScopeValue($scope, $numbers);
                    break;

                case 'text':
                    $value = post('options.value.' . $scope->scopeName) ?: null;
                    $this->setScopeValue($scope, $value);
                    break;
            }
        }

        $this->clearDependentScopes($scope->scopeName);

        /*
         * Trigger class event, merge results as viewable array
         */
        $params = func_get_args();

        $result = $this->fireEvent('filter.update', [$params]);

        if ($result && is_array($result)) {
            $result = call_user_func_array('array_merge', $result);
        }
        else {
            $result = [];
        }

        if ($scopeFilterWidget) {
            $scopeFilterWidget = $this->makeScopeFilterWidget($scope);
            $result['scopeName'] = $scope->scopeName;
            $result['scopeIsActive'] = $scope->scopeValue !== null && $scope->scopeValue !== [];

            if (method_exists($scopeFilterWidget, 'getActiveLabel')) {
                $result['scopeActiveLabel'] = $scopeFilterWidget->getActiveLabel();
            }
        }

        return $result ?: null;
    }

    /**
     * Returns available options for group scope type.
     * @return array
     */
    public function onFilterGetOptions()
    {
        $this->defineFilterScopes();

        $searchQuery = post('search');
        if (!$scopeName = post('scopeName')) {
            return;
        }

        $scope = $this->getScope($scopeName);
        $activeKeys = $scope->value ? array_keys($scope->value) : [];
        $available = $this->getAvailableOptions($scope, $searchQuery);
        $active = $searchQuery ? [] : $this->filterActiveOptions($activeKeys, $available);

        return [
            'scopeName' => $scopeName,
            'options' => [
                'available' => $this->optionsToAjax($available),
                'active'    => $this->optionsToAjax($active),
            ]
        ];
    }

    /**
     * Renders the form for a custom filter widget.
     *
     * @return array|null
     */
    public function onFilterRenderForm()
    {
        $this->defineFilterScopes();

        if (!$scopeName = post('scopeName')) {
            return null;
        }

        $scope = $this->getScope($this->normalizeScopeName($scopeName));

        if (!$this->isFilterWidget($scope)) {
            return null;
        }

        return [
            'scopeName' => $scope->scopeName,
            'html' => $this->makeScopeFilterWidget($scope)->renderForm(),
        ];
    }

    //
    // Internals
    //

    /**
     * Returns the available options a scope can use, either from the
     * model relation or from a supplied array. Optionally apply a search
     * constraint to the options.
     * @param  \Backend\Classes\Filter $scope
     * @param  string $searchQuery
     * @return array
     */
    protected function getAvailableOptions($scope, $searchQuery = null)
    {
        if ($scope->options) {
            return $this->getOptionsFromArray($scope, $searchQuery);
        }

        $available = [];
        $nameColumn = $this->getScopeNameFrom($scope);
        $options = $this->getOptionsFromModel($scope, $searchQuery);
        foreach ($options as $option) {
            $available[$option->getKey()] = $option->{$nameColumn};
        }

        return $available;
    }

    /**
     * Removes any already selected options from the available options, returns
     * a newly built array.
     * @param  array  $activeKeys
     * @param  array  $availableOptions
     * @return array
     */
    protected function filterActiveOptions(array $activeKeys, array &$availableOptions)
    {
        $active = [];
        foreach ($availableOptions as $id => $option) {
            if (!in_array($id, $activeKeys)) {
                continue;
            }

            $active[$id] = $option;
            unset($availableOptions[$id]);
        }

        return $active;
    }

    /**
     * Looks at the model for defined scope items.
     *
     * @param \Backend\Classes\FilterScope $scope
     * @param string|null $searchQuery
     * @return Collection
     */
    protected function getOptionsFromModel($scope, $searchQuery = null)
    {
        $model = $this->scopeModels[$scope->scopeName];

        $query = $model->newQuery();

        // @todo Implement lazy-loading of options
        $query->limit(250);

        /**
         * @event backend.filter.extendQuery
         * Provides an opportunity to extend the query of the list of options
         *
         * Example usage:
         *
         *     Event::listen('backend.filter.extendQuery', function ((\Backend\Widgets\Filter) $filterWidget, $query, (\Backend\Classes\FilterScope) $scope) {
         *         if ($scope->scopeName == 'status') {
         *             $query->where('status', '<>', 'all');
         *         }
         *     });
         *
         * Or
         *
         *     $listWidget->bindEvent('filter.extendQuery', function ($query, (\Backend\Classes\FilterScope) $scope) {
         *         if ($scope->scopeName == 'status') {
         *             $query->where('status', '<>', 'all');
         *         }
         *     });
         *
         */
        $this->fireSystemEvent('backend.filter.extendQuery', [$query, $scope]);

        if (!$searchQuery) {
            // If scope has active filter(s) run additional query and merge it with base query
            if ($scope->value) {
                $modelIds = array_keys($scope->value);
                $activeOptions = $model::findMany($modelIds);
            }

            $modelOptions = isset($activeOptions)
                ? $query->get()->merge($activeOptions)
                : $query->get();

            return $modelOptions;
        }

        $searchFields = [$model->getKeyName(), $this->getScopeNameFrom($scope)];
        return $query->searchWhere($searchQuery, $searchFields)->get();
    }

    /**
     * Look at the defined set of options for scope items, or the model method.
     * @return array
     */
    protected function getOptionsFromArray($scope, $searchQuery = null)
    {
        /*
         * Load the data
         */
        $options = $scope->options;

        if (is_scalar($options)) {
            $model = $this->scopeModels[$scope->scopeName];
            $methodName = $options;

            if (str_contains($methodName, '::')) {
                $options = Lang::get($methodName);
                if (!is_array($options)) {
                    $options = [];
                }
            } else {
                if (!$model->methodExists($methodName)) {
                    throw new ApplicationException(Lang::get('backend::lang.filter.options_method_not_exists', [
                        'model'  => get_class($model),
                        'method' => $methodName,
                        'filter' => $scope->scopeName
                    ]));
                }

                if (!empty($scope->dependsOn)) {
                    $options = $model->$methodName($this->getScopes());
                } else {
                    $options = $model->$methodName();
                }
            }
        }
        elseif (!is_array($options)) {
            $options = [];
        }

        /*
         * Apply the search
         */
        $searchQuery = Str::lower($searchQuery);
        if (strlen($searchQuery)) {
            $options = $this->filterOptionsBySearch($options, $searchQuery);
        }

        return $options;
    }

    /**
     * Filters an array of options by a search term.
     * @param array $options
     * @param string $query
     * @return array
     */
    protected function filterOptionsBySearch($options, $query)
    {
        $filteredOptions = [];

        $optionMatchesSearch = function ($words, $option) {
            foreach ($words as $word) {
                $word = trim($word);
                if (!strlen($word)) {
                    continue;
                }

                if (!Str::contains(Str::lower($option), $word)) {
                    return false;
                }
            }

            return true;
        };

        /*
         * Exact
         */
        foreach ($options as $index => $option) {
            if (Str::is(Str::lower($option), $query)) {
                $filteredOptions[$index] = $option;
                unset($options[$index]);
            }
        }

        /*
         * Fuzzy
         */
        $words = explode(' ', $query);
        foreach ($options as $index => $option) {
            if ($optionMatchesSearch($words, $option)) {
                $filteredOptions[$index] = $option;
            }
        }

        return $filteredOptions;
    }

    /**
     * Creates a flat array of filter scopes from the configuration.
     */
    protected function defineFilterScopes()
    {
        if ($this->scopesDefined) {
            return;
        }

        /**
         * @event backend.filter.extendScopesBefore
         * Provides an opportunity to interact with the Filter widget before defining the filter scopes
         *
         * Example usage:
         *
         *     Event::listen('backend.filter.extendScopesBefore', function ((\Backend\Widgets\Filter) $filterWidget) {
         *         // Just in case you really had to do something before scopes are defined
         *     });
         *
         * Or
         *
         *     $listWidget->bindEvent('filter.extendScopesBefore', function () use ((\Backend\Widgets\Filter) $filterWidget) {
         *         // Just in case you really had to do something before scopes are defined
         *     });
         *
         */
        $this->fireSystemEvent('backend.filter.extendScopesBefore');

        /*
         * All scopes
         */
        if (!isset($this->scopes) || !is_array($this->scopes)) {
            $this->scopes = [];
        }

        $this->addScopes($this->scopes);

        /**
         * @event backend.filter.extendScopes
         * Provides an opportunity to interact with the Filter widget & its scopes after the filter scopes have been initialized
         *
         * Example usage:
         *
         *     Event::listen('backend.filter.extendScopes', function ((\Backend\Widgets\Filter) $filterWidget) {
         *         $filterWidget->addScopes([
         *             'my_scope' => [
         *                 'label' => 'My Filter Scope'
         *             ]
         *         ]);
         *     });
         *
         * Or
         *
         *     $listWidget->bindEvent('filter.extendScopes', function () use ((\Backend\Widgets\Filter) $filterWidget) {
         *         $filterWidget->removeScope('my_scope');
         *     });
         *
         */
        $this->fireSystemEvent('backend.filter.extendScopes');

        $this->scopesDefined = true;
    }

    /**
     * Programatically add scopes, used internally and for extensibility.
     */
    public function addScopes(array $scopes)
    {
        foreach ($scopes as $name => $config) {
            /*
             * Check if user has permissions to show this filter
             */
            $permissions = array_get($config, 'permissions');
            if (!empty($permissions) && !BackendAuth::getUser()->hasAccess($permissions, false)) {
                continue;
            }

            $scopeObj = $this->makeFilterScope($name, $config);

            /*
             * Check that the filter scope matches the active context
             */
            if ($scopeObj->context !== null) {
                $context = is_array($scopeObj->context) ? $scopeObj->context : [$scopeObj->context];
                if (!in_array($this->getContext(), $context)) {
                    continue;
                }
            }

            /*
             * Validate scope model
             */
            if (isset($config['modelClass'])) {
                $class = $config['modelClass'];
                $model = new $class;
                $this->scopeModels[$name] = $model;
            }
            elseif ($model = $this->getModel()) {
                $this->scopeModels[$name] = $model;
            }

            /*
             * Ensure scope type options are set
             */
            $scopeProperties = [];
            switch ($scopeObj->type) {
                case 'date':
                case 'daterange':
                    $scopeProperties = [
                        'minDate'   => '2000-01-01',
                        'maxDate'   => '2099-12-31',
                        'firstDay'  => 0,
                        'yearRange' => 10,
                        'ignoreTimezone' => false,
                    ];

                    break;
            }

            foreach ($scopeProperties as $property => $value) {
                if (isset($config[$property])) {
                    $value = $config[$property];
                }

                $scopeObj->{$property} = $value;
            }

            $this->allScopes[$name] = $scopeObj;
        }
    }

    /**
     * Programatically remove a scope, used for extensibility.
     * @param string $scopeName Scope name
     */
    public function removeScope($scopeName)
    {
        if (isset($this->allScopes[$scopeName])) {
            unset($this->allScopes[$scopeName]);
        }
    }

    /**
     * Creates a filter scope object from name and configuration.
     */
    protected function makeFilterScope($name, $config)
    {
        $label = $config['label'] ?? null;
        $scopeType = $config['type'] ?? null;

        $scope = new FilterScope($name, $label);
        $scope->displayAs($scopeType, $config);
        $scope->idPrefix = $this->alias;

        /*
         * Set scope value
         */
        $scope->setScopeValue($this->getScopeValue($scope, @$config['default']));

        return $scope;
    }

    //
    // Filter query logic
    //

    /**
     * Applies all scopes to a DB query.
     * @param  Builder $query
     * @return Builder
     */
    public function applyAllScopesToQuery($query)
    {
        $this->defineFilterScopes();

        foreach ($this->allScopes as $scope) {
            // Ensure that only valid values are set scopes of type: group
            if ($scope->type === 'group' && !$this->isFilterWidget($scope)) {
                $activeKeys = $scope->value ? array_keys($scope->value) : [];
                $available = $this->getAvailableOptions($scope);
                $active = $this->filterActiveOptions($activeKeys, $available);
                $value = !empty($active) ? $active : null;
                $this->setScopeValue($scope, $value);
            }

            $this->applyScopeToQuery($scope, $query);
        }

        return $query;
    }

    /**
     * Applies a filter scope constraints to a DB query.
     * @param  string $scope
     * @param  Builder $query
     * @return Builder
     */
    public function applyScopeToQuery($scope, $query)
    {
        if (is_string($scope)) {
            $scope = $this->getScope($scope);
        }

        if ($this->isFilterWidget($scope)) {
            if ($scope->scopeValue === null || $scope->scopeValue === []) {
                return;
            }

            $this->makeScopeFilterWidget($scope)->applyScopeToQuery($query);
            return $query;
        }

        if (!$scope->value) {
            return;
        }

        switch ($scope->type) {
            case 'date':
                if ($scope->value instanceof Carbon) {
                    $value = $scope->value;

                    /*
                     * Condition
                     */
                    if ($scopeConditions = $scope->conditions) {
                        [$sql, $bindings] = $this->processConditionBindings($scopeConditions, [
                            'filtered' => $value->format('Y-m-d'),
                            'after'    => $value->format('Y-m-d H:i:s'),
                            'before'   => $value->copy()->addDay()->addMinutes(-1)->format('Y-m-d H:i:s'),
                        ]);

                        $query->whereRaw(DbDongle::parse($sql), $bindings);
                    }
                    /*
                     * Scope
                     */
                    elseif ($scopeMethod = $scope->scope) {
                        $query->$scopeMethod($value);
                    }
                }

                break;

            case 'daterange':
                if (is_array($scope->value) && count($scope->value) > 1) {
                    list($after, $before) = array_values($scope->value);

                    if ($after && $after instanceof Carbon && $before && $before instanceof Carbon) {
                        /*
                         * Condition
                         */
                        if ($scopeConditions = $scope->conditions) {
                            [$sql, $bindings] = $this->processConditionBindings($scopeConditions, [
                                'afterDate'  => $after->format('Y-m-d'),
                                'after'      => $after->format('Y-m-d H:i:s'),
                                'beforeDate' => $before->format('Y-m-d'),
                                'before'     => $before->format('Y-m-d H:i:s'),
                            ]);

                            $query->whereRaw(DbDongle::parse($sql), $bindings);
                        }
                        /*
                         * Scope
                         */
                        elseif ($scopeMethod = $scope->scope) {
                            $query->$scopeMethod($after, $before);
                        }
                    }
                }

                break;

            case 'number':
                if (is_numeric($scope->value)) {
                    /*
                     * Condition
                     */
                    if ($scopeConditions = $scope->conditions) {
                        [$sql, $bindings] = $this->processConditionBindings($scopeConditions, [
                            'filtered' => (float) $scope->value,
                        ]);

                        $query->whereRaw(DbDongle::parse($sql), $bindings);
                    }
                    /*
                     * Scope
                     */
                    elseif ($scopeMethod = $scope->scope) {
                        $query->$scopeMethod($scope->value);
                    }
                }

                break;

            case 'numberrange':
                if (is_array($scope->value) && count($scope->value) > 1) {
                    list($min, $max) = array_values($scope->value);

                    if (isset($min) || isset($max)) {
                        /*
                         * Condition
                         */
                        if ($scopeConditions = $scope->conditions) {
                            [$sql, $bindings] = $this->processConditionBindings($scopeConditions, [
                                'min' => $min === null ? -2147483647 : (float) $min,
                                'max' => $max === null ? 2147483647 : (float) $max,
                            ]);

                            $query->whereRaw(DbDongle::parse($sql), $bindings);
                        }
                        /*
                         * Scope
                         */
                        elseif ($scopeMethod = $scope->scope) {
                            $query->$scopeMethod($min, $max);
                        }
                    }
                }

                break;

            case 'text':
                /*
                 * Condition
                 */
                if ($scopeConditions = $scope->conditions) {
                    $query->whereRaw(DbDongle::parse(strtr($scopeConditions, [
                        ':value' => DB::getPdo()->quote($scope->value),
                    ])));
                }

                /*
                 * Scope
                 */
                elseif ($scopeMethod = $scope->scope) {
                    $query->$scopeMethod($scope->value);
                }

                break;

            default:
                $value = is_array($scope->value) ? array_keys($scope->value) : $scope->value;

                if (empty($value)) {
                    break;
                }

                /*
                 * Condition
                 */
                if ($scopeConditions = $scope->conditions) {
                    /*
                     * Switch scope: multiple conditions, value either 1 or 2
                     */
                    if (is_array($scopeConditions)) {
                        $conditionNum = is_array($value) ? 0 : $value - 1;
                        list($scopeConditions) = array_slice($scopeConditions, $conditionNum);
                    }

                    if (is_array($value)) {
                        $filtered = implode(',', array_build($value, function ($key, $_value) {
                            return [$key, DB::getPdo()->quote($_value)];
                        }));
                    }
                    else {
                        $filtered = DB::getPdo()->quote($value);
                    }

                    $query->whereRaw(DbDongle::parse(strtr($scopeConditions, [':filtered' => $filtered])));
                }
                /*
                 * Scope
                 */
                elseif ($scopeMethod = $scope->scope) {
                    $query->$scopeMethod($value);
                }

                break;
        }

        return $query;
    }

    //
    // Access layer
    //

    /**
     * Returns a scope value for this widget instance.
     */
    public function getScopeValue($scope, $default = null)
    {
        if (is_string($scope)) {
            $scope = $this->getScope($scope);
        }

        $cacheKey = 'scope-'.$scope->scopeName;
        return $this->getSession($cacheKey, $default);
    }

    /**
     * Sets an scope value for this widget instance.
     */
    public function setScopeValue($scope, $value)
    {
        if (is_string($scope)) {
            $scope = $this->getScope($scope);
        }

        $cacheKey = 'scope-'.$scope->scopeName;
        $this->putSession($cacheKey, $value);

        $scope->setScopeValue($value);
    }

    /**
     * Get all the registered scopes for the instance.
     * @return array
     */
    public function getScopes()
    {
        return $this->allScopes;
    }

    /**
     * Get a specified scope object
     * @param  string $scope
     * @return mixed
     */
    public function getScope($scope)
    {
        $scope = $this->normalizeScopeName($scope);

        if (!isset($this->allScopes[$scope])) {
            throw new ApplicationException('No definition for scope ' . $scope);
        }

        return $this->allScopes[$scope];
    }

    /**
     * Returns the display name column for a scope.
     * @param  string $scope
     * @return string
     */
    public function getScopeNameFrom($scope)
    {
        if (is_string($scope)) {
            $scope = $this->getScope($scope);
        }

        return $scope->nameFrom;
    }

    /**
     * Returns the active context for displaying the filter.
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Sets the list widget this filter is attached to.
     */
    public function setListWidget(Lists $listWidget)
    {
        $this->listWidget = $listWidget;

        if (!$this->model && $listWidget->model) {
            $this->model = $listWidget->model;
        }
    }

    /**
     * Returns the configured model, falling back to the attached list widget model.
     */
    public function getModel()
    {
        if ($this->model) {
            return $this->model;
        }

        return $this->listWidget ? $this->listWidget->model : null;
    }

    /**
     * Returns the header display value for a scope.
     *
     * @param \Backend\Classes\FilterScope|string $scope
     * @return string|null
     */
    public function getHeaderValue($scope)
    {
        if (is_string($scope)) {
            $scope = $this->getScope($scope);
        }

        return trans($scope->label);
    }

    /**
     * Returns a list of scope names backed by custom filter widgets.
     *
     * @return array
     */
    public function getFilterWidgetScopeNames()
    {
        $this->defineFilterScopes();

        $scopeNames = [];

        foreach ($this->allScopes as $scope) {
            if ($this->isFilterWidget($scope)) {
                $scopeNames[] = $scope->scopeName;
            }
        }

        return $scopeNames;
    }

    //
    // Helpers
    //

    /**
     * Returns true if the supplied scope uses a custom filter widget.
     */
    protected function isFilterWidget($scope)
    {
        if (is_string($scope)) {
            $scope = $this->getScope($scope);
        }

        $widgetClass = WidgetManager::instance()->resolveFilterWidget($scope->type);

        return class_exists($widgetClass) && is_subclass_of($widgetClass, FilterWidgetBase::class);
    }

    /**
     * Creates or returns a custom filter widget for a scope.
     */
    protected function makeScopeFilterWidget($scope)
    {
        if (is_string($scope)) {
            $scope = $this->getScope($scope);
        }

        if (isset($this->filterWidgets[$scope->scopeName])) {
            return $this->filterWidgets[$scope->scopeName];
        }

        $widgetClass = WidgetManager::instance()->resolveFilterWidget($scope->type);
        $widgetConfig = (array) $scope->config;
        $widgetConfig['model'] = $this->scopeModels[$scope->scopeName] ?? $this->getModel();
        $widgetConfig['parentFilter'] = $this;
        $widgetConfig['alias'] = $this->alias . ucfirst($scope->scopeName);

        return $this->filterWidgets[$scope->scopeName] = $this->makeFilterScopeWidget($widgetClass, $scope, $widgetConfig);
    }

    /**
     * Normalizes postback names such as Filter[status] to status.
     */
    protected function normalizeScopeName($scope)
    {
        if (preg_match('/^Filter\[([^\]]+)\]$/', (string) $scope, $matches)) {
            return $matches[1];
        }

        return $scope;
    }

    /**
     * Clears scopes that depend on the supplied scope so session state and UI state stay aligned.
     */
    protected function clearDependentScopes($scopeName, array &$visited = [])
    {
        $scopeName = $this->normalizeScopeName($scopeName);

        if (isset($visited[$scopeName])) {
            return;
        }

        $visited[$scopeName] = true;

        foreach ($this->allScopes as $dependentScope) {
            if (!$dependentScope->dependsOn) {
                continue;
            }

            $dependsOn = is_array($dependentScope->dependsOn)
                ? $dependentScope->dependsOn
                : [$dependentScope->dependsOn];

            $dependsOn = array_map([$this, 'normalizeScopeName'], $dependsOn);

            if (!in_array($scopeName, $dependsOn, true)) {
                continue;
            }

            $this->setScopeValue($dependentScope, null);
            $this->clearDependentScopes($dependentScope->scopeName, $visited);
        }
    }

    /**
     * Convert a key/pair array to a named array {id: 1, name: 'Foobar'}
     * @param  array $options
     * @return array
     */
    protected function optionsToAjax($options)
    {
        $processed = [];
        foreach ($options as $id => $result) {
            $processed[] = ['id' => $id, 'name' => trans($result)];
        }
        return $processed;
    }

    /**
     * Convert a named array to a key/pair array
     * @param  array $options
     * @return array
     */
    protected function optionsFromAjax($options)
    {
        $processed = [];
        if (!is_array($options)) {
            return $processed;
        }

        foreach ($options as $option) {
            $id = array_get($option, 'id');
            if ($id === null) {
                continue;
            }
            $processed[$id] = array_get($option, 'name');
        }
        return $processed;
    }

    /**
     * Convert an array from the posted dates
     *
     * @param  array $dates
     *
     * @return array
     */
    protected function datesFromAjax($ajaxDates)
    {
        $dates = [];
        $dateRegex = '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/';

        if (null !== $ajaxDates) {
            if (!is_array($ajaxDates)) {
                if (preg_match($dateRegex, $ajaxDates)) {
                    $dates = [$ajaxDates];
                }
            } else {
                foreach ($ajaxDates as $i => $date) {
                    if (preg_match($dateRegex, $date)) {
                        $dates[] = Carbon::createFromFormat('Y-m-d H:i:s', $date);
                    } elseif (empty($date)) {
                        if ($i == 0) {
                            $dates[] = Carbon::createFromFormat('Y-m-d H:i:s', '0000-01-01 00:00:00');
                        } else {
                            $dates[] = Carbon::createFromFormat('Y-m-d H:i:s', '2999-12-31 23:59:59');
                        }
                    } else {
                        $dates = [];
                        break;
                    }
                }
            }
        }
        return $dates;
    }

    /**
     * Convert an array from the posted numbers
     *
     * @param  array $dates
     *
     * @return array
     */
    protected function numbersFromAjax($ajaxNumbers)
    {
        $numbers = [];

        if (!empty($ajaxNumbers)) {
            if (!is_array($ajaxNumbers)) {
                if (is_numeric($ajaxNumbers)) {
                    $numbers = [(float) $ajaxNumbers];
                }
            } else {
                foreach ($ajaxNumbers as $number) {
                    if (is_numeric($number)) {
                        $numbers[] = (float) $number;
                    } else {
                        $numbers[] = null;
                    }
                }
            }
        }

        return $numbers;
    }

    /**
     * Converts named :placeholder parameters in a conditions string to
     * positional ? parameters and returns the modified SQL along with an
     * ordered bindings array. Only placeholders present in the provided
     * named bindings are replaced; unknown placeholders are left as-is.
     *
     * Laravel's query builder does not support named PDO parameters in
     * whereRaw(), so this conversion is required.
     *
     * @return array{string, array}  [processedSql, orderedBindings]
     */
    protected function processConditionBindings(string $conditions, array $namedBindings): array
    {
        $orderedBindings = [];

        // Match ':placeholder' (quoted) or :placeholder (unquoted).
        // Existing plugin configs may wrap placeholders in SQL quotes
        // (e.g. "expenses >= ':min'"), which must be stripped so PDO
        // sees a real ? parameter instead of a quoted literal '?'.
        $processedSql = preg_replace_callback("/(?:':(\\w+)'|:(\\w+))/", function ($matches) use ($namedBindings, &$orderedBindings) {
            $name = $matches[1] !== '' ? $matches[1] : $matches[2];
            if (array_key_exists($name, $namedBindings)) {
                $orderedBindings[] = $namedBindings[$name];
                return '?';
            }
            return $matches[0];
        }, $conditions);

        return [$processedSql, $orderedBindings];
    }

    /**
     * @param mixed $scope
     *
     * @return string
     */
    protected function getFilterDateFormat($scope)
    {
        if (isset($scope->date_format)) {
            return $scope->date_format;
        }

        return trans('backend::lang.filter.date.format');
    }
}
