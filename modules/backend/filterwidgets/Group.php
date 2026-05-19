<?php namespace Backend\FilterWidgets;

use Backend\Classes\FilterWidgetBase;
use Illuminate\Support\Facades\DB;
use Lang;
use Str;
use Winter\Storm\Exception\ApplicationException;
use Winter\Storm\Support\Facades\DbDongle;

/**
 * Group filter widget.
 */
class Group extends FilterWidgetBase
{
    public const MODE_EXCLUDE = 'exclude';
    public const MODE_INCLUDE = 'include';

    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('group');
    }

    public function renderForm()
    {
        $this->prepareVars();
        return $this->makePartial('group_form');
    }

    public function prepareVars()
    {
        $this->vars['scope'] = $this->filterScope;
        $this->vars['options'] = $this->getAvailableOptions();
        $this->vars['activeLabel'] = $this->getActiveLabel($this->vars['options']);
    }

    public function getActiveValue()
    {
        if (post('clearScope')) {
            return null;
        }

        $value = post('Filter');
        $selected = $value['value'] ?? [];

        if (!is_array($selected) || empty($selected)) {
            return null;
        }

        $value['value'] = array_combine($selected, $selected);
        $value['mode'] = $value['mode'] ?? self::MODE_INCLUDE;

        return $value;
    }

    public function applyScopeToQuery($query)
    {
        $scope = $this->filterScope;

        if ($this->applyModelScopeToQuery($query, array_keys((array) $scope->value))) {
            return;
        }

        $activeValue = (array) $scope->value;
        if (!count($activeValue)) {
            return;
        }

        if (is_string($scope->conditions)) {
            $filtered = implode(',', array_map(fn ($value) => DB::getPdo()->quote($value), $activeValue));
            $query->whereRaw(DbDongle::parse(strtr($scope->conditions, [
                ':filtered' => $filtered,
                ':value' => $filtered,
            ])));
            return;
        }

        $action = $scope->mode === self::MODE_EXCLUDE ? 'whereNotIn' : 'whereIn';
        $query->{$action}($this->valueFrom, $activeValue);
    }

    protected function getAvailableOptions(?string $searchQuery = null): array
    {
        $scope = $this->filterScope;

        if ($scope->options || $scope->optionsMethod) {
            return $this->getOptionsFromArray($searchQuery);
        }

        if (!$this->model) {
            return [];
        }

        $query = $this->model->newQuery();

        if ($scope->optionsScope) {
            $query->{$scope->optionsScope}();
        }

        $query->limit(250);

        if ($searchQuery) {
            $query->searchWhere($searchQuery, [$this->model->getKeyName(), $scope->nameFrom]);
        }

        $available = [];
        foreach ($query->get() as $option) {
            $available[$option->getKey()] = $option->{$scope->nameFrom};
        }

        return $available;
    }

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

    public function getActiveLabel(?array $options = null): string
    {
        $scope = $this->filterScope;
        $options = $options ?? $this->getAvailableOptions();

        if (!$scope->scopeValue) {
            return trans('backend::lang.filter.all');
        }

        $values = array_keys((array) $scope->value);

        if ($scope->displayValues === 'count') {
            return (string) count($values);
        }

        if ($scope->displayValues === 'key') {
            return implode(', ', $values);
        }

        $labels = [];
        foreach ($values as $value) {
            $labels[] = isset($options[$value])
                ? trans($options[$value])
                : $value;
        }

        return implode(', ', $labels);
    }
}
