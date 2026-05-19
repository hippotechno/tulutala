<?php namespace Backend\FilterWidgets;

use Backend\Classes\FilterWidgetBase;

/**
 * Number filter widget.
 */
class Number extends FilterWidgetBase
{
    public const CONDITION_EQUALS = 'equals';
    public const CONDITION_BETWEEN = 'between';
    public const CONDITION_GREATER = 'greater';
    public const CONDITION_LESSER = 'lesser';

    public function init()
    {
        if (!$this->filterScope->conditions) {
            $this->filterScope->conditions = [
                self::CONDITION_EQUALS => true,
            ];
        }
        elseif (is_string($this->filterScope->conditions)) {
            $this->filterScope->conditions = [
                self::CONDITION_EQUALS => $this->filterScope->conditions,
            ];
        }

        if (!$this->filterScope->condition) {
            $this->filterScope->condition = array_key_first((array) $this->filterScope->conditions);
        }
    }

    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('number');
    }

    public function renderForm()
    {
        $this->prepareVars();
        return $this->makePartial('number_form');
    }

    public function prepareVars()
    {
        $this->vars['scope'] = $this->filterScope;
    }

    public function getActiveValue()
    {
        if (post('clearScope')) {
            return null;
        }

        $value = post('Filter');
        $condition = post('Filter[condition]');

        if ($condition === self::CONDITION_BETWEEN) {
            if (!$this->hasPostValue('min') && !$this->hasPostValue('max')) {
                return null;
            }

            if (!$this->hasPostValue('min')) {
                $value['value'] = post('Filter[max]');
                $value['condition'] = self::CONDITION_LESSER;
            }
            elseif (!$this->hasPostValue('max')) {
                $value['value'] = post('Filter[min]');
                $value['condition'] = self::CONDITION_GREATER;
            }
        }
        elseif (!$this->hasPostValue('value')) {
            return null;
        }

        return $value;
    }

    public function applyScopeToQuery($query)
    {
        $scope = $this->filterScope;

        if ($this->applyModelScopeToQuery($query)) {
            return;
        }

        $conditions = (array) $scope->conditions;
        $condition = $scope->condition ?: self::CONDITION_EQUALS;

        if (isset($conditions[$condition]) && is_string($conditions[$condition])) {
            [$sql, $bindings] = $this->processConditionBindings($conditions[$condition], [
                'filtered' => $this->parseNumber($scope->value),
                'value' => $this->parseNumber($scope->value),
                'min' => $this->parseNumber($scope->min),
                'max' => $this->parseNumber($scope->max),
            ]);
            $query->whereRaw($sql, $bindings);
            return;
        }

        if ($condition === self::CONDITION_BETWEEN) {
            $query
                ->where($this->valueFrom, '>=', $scope->min)
                ->where($this->valueFrom, '<=', $scope->max);
            return;
        }

        if ($condition === self::CONDITION_GREATER) {
            $query->where($this->valueFrom, '>=', $scope->value);
            return;
        }

        if ($condition === self::CONDITION_LESSER) {
            $query->where($this->valueFrom, '<=', $scope->value);
            return;
        }

        $query->where($this->valueFrom, $scope->value);
    }

    protected function parseNumber($value)
    {
        return is_numeric($value) ? +$value : null;
    }

    public function getConditionLabel($condition)
    {
        return match ($condition) {
            self::CONDITION_BETWEEN => 'is between',
            self::CONDITION_GREATER => 'is greater than',
            self::CONDITION_LESSER => 'is less than',
            default => 'is equal to',
        };
    }

    public function hasActiveValue(): bool
    {
        return $this->filterScope->scopeValue !== null && $this->filterScope->scopeValue !== [];
    }

    public function getActiveLabel()
    {
        if (!$this->hasActiveValue()) {
            return trans('backend::lang.filter.number_all');
        }

        $scope = $this->filterScope;
        $condition = $scope->condition ?: self::CONDITION_EQUALS;

        if ($condition === self::CONDITION_BETWEEN) {
            return trim($scope->min . ' - ' . $scope->max);
        }

        if ($condition === self::CONDITION_GREATER) {
            return '>= ' . $scope->value;
        }

        if ($condition === self::CONDITION_LESSER) {
            return '<= ' . $scope->value;
        }

        return $scope->value;
    }
}
