<?php namespace Backend\FilterWidgets;

use Backend\Classes\FilterWidgetBase;
use Carbon\Carbon;

/**
 * Date filter widget.
 */
class Date extends FilterWidgetBase
{
    public const CONDITION_EQUALS = 'equals';
    public const CONDITION_NOT_EQUALS = 'notEquals';
    public const CONDITION_BETWEEN = 'between';
    public const CONDITION_BEFORE = 'before';
    public const CONDITION_AFTER = 'after';

    public function init()
    {
        if (!$this->filterScope->conditions || is_string($this->filterScope->conditions)) {
            $this->filterScope->conditions = [
                self::CONDITION_EQUALS => $this->filterScope->conditions ?: true,
                self::CONDITION_NOT_EQUALS => true,
                self::CONDITION_BETWEEN => true,
                self::CONDITION_BEFORE => true,
                self::CONDITION_AFTER => true,
            ];
        }

        if (!$this->filterScope->condition) {
            $this->filterScope->condition = array_key_first((array) $this->filterScope->conditions);
        }
    }

    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('date');
    }

    public function renderForm()
    {
        $this->prepareVars();
        return $this->makePartial('date_form');
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
            if (!$this->hasPostValue('after') && !$this->hasPostValue('before')) {
                return null;
            }

            if (!$this->hasPostValue('after')) {
                $value['value'] = post('Filter[before]');
                $value['condition'] = self::CONDITION_BEFORE;
            }
            elseif (!$this->hasPostValue('before')) {
                $value['value'] = post('Filter[after]');
                $value['condition'] = self::CONDITION_AFTER;
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
                'filtered' => $this->parseDate($scope->value),
                'value' => $this->parseDate($scope->value),
                'valueDate' => $this->parseDate($scope->value, true),
                'after' => $this->parseDate($scope->after),
                'afterDate' => $this->parseDate($scope->after, true),
                'before' => $this->parseDate($scope->before, false, true),
                'beforeDate' => $this->parseDate($scope->before, true),
            ]);
            $query->whereRaw($sql, $bindings);
            return;
        }

        if ($condition === self::CONDITION_NOT_EQUALS) {
            $query->where(function ($query) use ($scope) {
                $query
                    ->where($this->valueFrom, '>', $this->parseDate($scope->value, false, true, true))
                    ->orWhere($this->valueFrom, '<', $this->parseDate($scope->value, false, false, true));
            });
            return;
        }

        if ($condition === self::CONDITION_BETWEEN) {
            $query
                ->where($this->valueFrom, '>=', $this->parseDate($scope->after, false, false, true))
                ->where($this->valueFrom, '<=', $this->parseDate($scope->before, false, true, true));
            return;
        }

        if ($condition === self::CONDITION_AFTER) {
            $query->where($this->valueFrom, '>=', $this->parseDate($scope->value, false, false, true));
            return;
        }

        if ($condition === self::CONDITION_BEFORE) {
            $query->where($this->valueFrom, '<=', $this->parseDate($scope->value, false, true, true));
            return;
        }

        $query
            ->where($this->valueFrom, '>=', $this->parseDate($scope->value, false, false, true))
            ->where($this->valueFrom, '<=', $this->parseDate($scope->value, false, true, true));
    }

    protected function parseDate($value, bool $dateOnly = false, bool $endOfDay = false, bool $returnObject = false)
    {
        if (!$value) {
            return null;
        }

        $date = strtolower((string) $value) === 'now'
            ? Carbon::now()
            : Carbon::parse($value);

        $date = $endOfDay ? $date->copy()->endOfDay() : $date->copy()->startOfDay();

        if ($returnObject) {
            return $date;
        }

        return $dateOnly ? $date->format('Y-m-d') : $date->format('Y-m-d H:i:s');
    }

    public function getConditionLabel($condition)
    {
        return match ($condition) {
            self::CONDITION_NOT_EQUALS => 'not equal to',
            self::CONDITION_BETWEEN => 'is between',
            self::CONDITION_BEFORE => 'is before',
            self::CONDITION_AFTER => 'is after',
            default => 'is equal to',
        };
    }
}
