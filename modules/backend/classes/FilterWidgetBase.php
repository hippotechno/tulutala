<?php namespace Backend\Classes;

use Winter\Storm\Support\Facades\DbDongle;
use Illuminate\Support\Facades\DB;
use Lang;
use Str;
use Winter\Storm\Exception\ApplicationException;

/**
 * Filter Widget base class
 * Widgets used specifically for filter scopes.
 *
 * @package winter\wn-backend-module
 * @author Alexey Bobkov, Samuel Georges
 */
abstract class FilterWidgetBase extends WidgetBase
{
    /**
     * @var \Winter\Storm\Database\Model|null Related model object for the filter.
     */
    public $model;

    /**
     * @var bool Determines if the filtered column is stored as JSON in the database.
     */
    public $isJsonable;

    /**
     * @var FilterScope Object containing general filter scope information.
     */
    protected $filterScope;

    /**
     * @var string Raw scope name.
     */
    protected $scopeName;

    /**
     * @var string Attribute value source.
     */
    protected $valueFrom;

    /**
     * @var \Backend\Widgets\Filter|null Parent filter that contains this scope.
     */
    protected $parentFilter = null;

    /**
     * Constructor.
     *
     * @param \Backend\Classes\Controller $controller Active controller object.
     * @param FilterScope $filterScope Object containing general filter scope information.
     * @param array $configuration Configuration that relates to this widget.
     */
    public function __construct($controller, $filterScope, $configuration = [])
    {
        $this->filterScope = $filterScope;
        $this->scopeName = $filterScope->scopeName;
        $this->valueFrom = $filterScope->valueFrom ?: $this->scopeName;
        $this->config = $this->makeConfig($configuration);

        $this->fillFromConfig([
            'model',
            'isJsonable',
            'parentFilter',
        ]);

        parent::__construct($controller, $configuration);
    }

    /**
     * Retrieve the parent filter for this filter widget.
     *
     * @return \Backend\Widgets\Filter|null
     */
    public function getParentFilter()
    {
        return $this->parentFilter;
    }

    /**
     * Render the form to use for filtering.
     *
     * @return string|null
     */
    public function renderForm()
    {
    }

    /**
     * Returns the HTML element field name for this widget.
     *
     * @return string
     */
    public function getScopeName()
    {
        return $this->filterScope->getName();
    }

    /**
     * Returns the value for this filter scope.
     *
     * @return mixed
     */
    public function getLoadValue()
    {
        return $this->filterScope->scopeValue;
    }

    /**
     * Looks up the scope header value.
     *
     * @return mixed
     */
    public function getHeaderValue()
    {
        return $this->getParentFilter()->getHeaderValue($this->filterScope);
    }

    /**
     * Returns the active posted value for this filter scope.
     *
     * @return mixed
     */
    public function getActiveValue()
    {
        if (post('clearScope')) {
            return null;
        }

        return post($this->getScopeName(), post('Filter'));
    }

    /**
     * Returns the filter scope object.
     *
     * @return FilterScope
     */
    public function getFilterScope()
    {
        return $this->filterScope;
    }

    /**
     * Applies this scope to the supplied query.
     *
     * @param mixed $query
     * @return void
     */
    public function applyScopeToQuery($query)
    {
    }

    /**
     * Returns true if a posted value exists for a nested filter input.
     */
    protected function hasPostValue($name): bool
    {
        $value = post(
            $this->getScopeName() . "[{$name}]",
            post("Filter[{$name}]")
        );

        if (is_array($value)) {
            return count($value) > 0;
        }

        return strlen(trim((string) $value)) > 0;
    }

    /**
     * Converts named placeholders to query builder bindings for whereRaw().
     *
     * @return array{string, array}
     */
    protected function processConditionBindings(string $conditions, array $namedBindings): array
    {
        $orderedBindings = [];

        $processedSql = preg_replace_callback("/(?:':(\\w+)'|:(\\w+))/", function ($matches) use ($namedBindings, &$orderedBindings) {
            $name = $matches[1] !== '' ? $matches[1] : $matches[2];
            if (array_key_exists($name, $namedBindings)) {
                $orderedBindings[] = $namedBindings[$name];
                return '?';
            }

            return $matches[0];
        }, $conditions);

        return [DbDongle::parse($processedSql), $orderedBindings];
    }

    /**
     * Applies a configured model scope when available.
     */
    protected function applyModelScopeToQuery($query, $value = null): bool
    {
        $scopeMethod = $this->filterScope->modelScope ?: $this->filterScope->scope;

        if (!$scopeMethod) {
            return false;
        }

        if (func_num_args() < 2) {
            $value = $this->filterScope->value;
        }

        $query->$scopeMethod($value);
        return true;
    }

    /**
     * Returns options for filter widgets that use an inline option list.
     */
    protected function getOptionsFromArray(?string $searchQuery = null): array
    {
        $scope = $this->filterScope;
        $options = $scope->optionsMethod ?: $scope->options;

        if (is_string($options)) {
            if (str_contains($options, '::') && is_callable($options)) {
                $options = $options($this->model, $scope);
            }
            elseif ($this->model && $this->model->methodExists($options)) {
                $options = $this->model->{$options}($this->getParentFilter()->getScopes());
            }
            elseif (str_contains($options, '::')) {
                $options = Lang::get($options);
            }
            else {
                throw new ApplicationException(Lang::get('backend::lang.filter.options_method_not_exists', [
                    'model' => $this->model ? get_class($this->model) : static::class,
                    'method' => $options,
                    'filter' => $scope->scopeName,
                ]));
            }
        }

        if (!is_array($options)) {
            return [];
        }

        if ($searchQuery !== null && strlen($searchQuery)) {
            $query = Str::lower($searchQuery);
            $options = array_filter($options, function ($option) use ($query) {
                return Str::contains(Str::lower((string) $option), $query);
            });
        }

        return $options;
    }

    /**
     * Applies the default option-style filter logic to a query.
     */
    protected function applyValueToQuery($query, $value = null): void
    {
        $scope = $this->filterScope;

        if (func_num_args() < 2) {
            $value = is_array($scope->value) ? array_keys($scope->value) : $scope->value;
        }

        if (empty($value) && $value !== '0' && $value !== 0) {
            return;
        }

        if ($scopeConditions = $scope->conditions) {
            if (is_array($scopeConditions)) {
                $conditionNum = is_array($value) ? 0 : $value - 1;
                list($scopeConditions) = array_slice($scopeConditions, $conditionNum);
            }

            if (is_array($value)) {
                $filtered = implode(',', array_map(fn ($item) => DB::getPdo()->quote($item), $value));
            }
            else {
                $filtered = DB::getPdo()->quote($value);
            }

            $query->whereRaw(DbDongle::parse(strtr($scopeConditions, [
                ':filtered' => $filtered,
                ':value' => $filtered,
            ])));
            return;
        }

        if ($this->applyModelScopeToQuery($query, $value)) {
            return;
        }

        if (is_array($value)) {
            $query->whereIn($this->valueFrom, $value);
            return;
        }

        $query->where($this->valueFrom, $value);
    }
}
