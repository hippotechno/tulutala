<?php namespace Backend\FilterWidgets;

use Backend\Classes\FilterWidgetBase;
use Illuminate\Support\Facades\DB;
use Winter\Storm\Support\Facades\DbDongle;

/**
 * Text filter widget.
 */
class Text extends FilterWidgetBase
{
    public const CONDITION_EQUALS = 'equals';
    public const CONDITION_CONTAINS = 'contains';

    public function init()
    {
        if (!$this->filterScope->conditions || is_string($this->filterScope->conditions)) {
            $this->filterScope->conditions = [
                self::CONDITION_EQUALS => $this->filterScope->conditions ?: true,
                self::CONDITION_CONTAINS => true,
            ];
        }

        if (!$this->filterScope->condition) {
            $this->filterScope->condition = array_key_first((array) $this->filterScope->conditions);
        }
    }

    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('text');
    }

    public function renderForm()
    {
        $this->prepareVars();
        return $this->makePartial('text_form');
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

        if (!$this->hasPostValue('value')) {
            return null;
        }

        return post('Filter');
    }

    public function applyScopeToQuery($query)
    {
        $scope = $this->filterScope;

        if ($this->applyModelScopeToQuery($query)) {
            return;
        }

        $conditions = (array) $scope->conditions;
        $condition = $scope->condition ?: self::CONDITION_EQUALS;
        $value = $scope->value;

        if (isset($conditions[$condition]) && is_string($conditions[$condition])) {
            $query->whereRaw(DbDongle::parse(strtr($conditions[$condition], [
                ':value' => DB::getPdo()->quote($value),
                ':filtered' => DB::getPdo()->quote($value),
            ])));
            return;
        }

        if ($condition === self::CONDITION_CONTAINS) {
            $query->where($this->valueFrom, 'LIKE', '%' . $value . '%');
            return;
        }

        $query->where($this->valueFrom, $value);
    }

    public function getConditionLabel($condition)
    {
        return match ($condition) {
            self::CONDITION_CONTAINS => 'contains',
            default => 'is equal to',
        };
    }
}
