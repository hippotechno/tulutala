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
    public const MODE_TOGGLE = 'toggle';

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
        $this->vars['activeMode'] = $this->getActiveMode();
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
        $value['mode'] = $this->getActiveMode($value['mode'] ?? null);

        return $value;
    }

    public function applyScopeToQuery($query)
    {
        $scope = $this->filterScope;

        $activeValue = (array) $scope->value;
        $activeMode = $this->getActiveMode();

        if ($this->applyModelScopeToQuery($query, array_keys($activeValue), $activeMode)) {
            return;
        }

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

        $action = $activeMode === self::MODE_EXCLUDE ? 'whereNotIn' : 'whereIn';
        $query->{$action}($this->valueFrom, array_keys($activeValue));
    }

    protected function applyModelScopeToQuery($query, $value = null, $mode = null): bool
    {
        $scopeMethod = $this->filterScope->modelScope ?: $this->filterScope->scope;

        if (!$scopeMethod) {
            return false;
        }

        $query->$scopeMethod($value, $mode ?: $this->getActiveMode());
        return true;
    }

    public function getActiveMode(?string $postedMode = null): string
    {
        $scope = $this->filterScope;
        $matchMode = in_array($scope->matchMode, [self::MODE_INCLUDE, self::MODE_EXCLUDE, self::MODE_TOGGLE], true)
            ? $scope->matchMode
            : self::MODE_INCLUDE;

        if ($matchMode === self::MODE_TOGGLE) {
            if (in_array($postedMode, [self::MODE_INCLUDE, self::MODE_EXCLUDE], true)) {
                return $postedMode;
            }

            return $scope->mode === self::MODE_EXCLUDE
                ? self::MODE_EXCLUDE
                : self::MODE_INCLUDE;
        }

        return $matchMode === self::MODE_EXCLUDE
            ? self::MODE_EXCLUDE
            : self::MODE_INCLUDE;
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
