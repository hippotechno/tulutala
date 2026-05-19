<?php namespace Backend\FilterWidgets;

use Backend\Classes\FilterWidgetBase;
use Backend\Facades\Backend;
use Carbon\Carbon;

/**
 * Date range filter widget.
 */
class DateRange extends FilterWidgetBase
{
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('daterange');
    }

    public function prepareVars()
    {
        $scope = $this->filterScope;
        $this->vars['scope'] = $scope;

        if (
            $scope->value
            && is_array($scope->value)
            && count($scope->value) === 2
            && $scope->value[0] instanceof Carbon
            && $scope->value[1] instanceof Carbon
        ) {
            $after = $scope->value[0]->format('Y-m-d H:i:s');
            $before = $scope->value[1]->format('Y-m-d H:i:s');

            if (strcasecmp($after, '0000-01-01 00:00:00') > 0) {
                $this->vars['afterStr'] = Backend::dateTime($scope->value[0], ['formatAlias' => 'dateMin']);
                $this->vars['after'] = $after;
            }
            else {
                $this->vars['afterStr'] = '-∞';
                $this->vars['after'] = null;
            }

            if (strcasecmp($before, '2999-12-31 23:59:59') < 0) {
                $this->vars['beforeStr'] = Backend::dateTime($scope->value[1], ['formatAlias' => 'dateMin']);
                $this->vars['before'] = $before;
            }
            else {
                $this->vars['beforeStr'] = '∞';
                $this->vars['before'] = null;
            }
        }
    }

    public function getActiveValue()
    {
        $data = json_decode(post('options'), true);
        $dates = $this->datesFromAjax($data['dates'] ?? null);

        if (!empty($dates)) {
            return $dates;
        }

        return null;
    }

    public function applyScopeToQuery($query)
    {
        $scope = $this->filterScope;

        if (!is_array($scope->value) || count($scope->value) <= 1) {
            return;
        }

        list($after, $before) = array_values($scope->value);

        if (!$after instanceof Carbon || !$before instanceof Carbon) {
            return;
        }

        if ($scopeConditions = $scope->conditions) {
            [$sql, $bindings] = $this->processConditionBindings($scopeConditions, [
                'afterDate' => $after->format('Y-m-d'),
                'after' => $after->format('Y-m-d H:i:s'),
                'beforeDate' => $before->format('Y-m-d'),
                'before' => $before->format('Y-m-d H:i:s'),
            ]);

            $query->whereRaw($sql, $bindings);
            return;
        }

        $scopeMethod = $scope->modelScope ?: $scope->scope;
        if ($scopeMethod) {
            $query->$scopeMethod($after, $before);
        }
    }

    protected function datesFromAjax($ajaxDates): array
    {
        $dates = [];
        $dateRegex = '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/';

        if ($ajaxDates === null) {
            return $dates;
        }

        if (!is_array($ajaxDates)) {
            return preg_match($dateRegex, $ajaxDates) ? [$ajaxDates] : [];
        }

        foreach ($ajaxDates as $i => $date) {
            if (preg_match($dateRegex, (string) $date)) {
                $dates[] = Carbon::createFromFormat('Y-m-d H:i:s', $date);
            }
            elseif (empty($date)) {
                $dates[] = Carbon::createFromFormat(
                    'Y-m-d H:i:s',
                    $i === 0 ? '0000-01-01 00:00:00' : '2999-12-31 23:59:59'
                );
            }
            else {
                return [];
            }
        }

        return $dates;
    }
}
