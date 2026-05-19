<?php namespace Backend\FilterWidgets;

use Backend\Classes\FilterWidgetBase;

/**
 * Number range filter widget.
 */
class NumberRange extends FilterWidgetBase
{
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('numberrange');
    }

    public function prepareVars()
    {
        $scope = $this->filterScope;

        $this->vars['scope'] = $scope;
        $this->vars['minValue'] = $this->getNumericConfig('min');
        $this->vars['maxValue'] = $this->getNumericConfig('max');
        $this->vars['step'] = $this->getNumericConfig('step');

        if (
            $scope->value
            && is_array($scope->value)
            && count($scope->value) === 2
            && (isset($scope->value[0]) || isset($scope->value[1]))
        ) {
            $min = $scope->value[0];
            $max = $scope->value[1];

            $this->vars['minStr'] = $min ?? '-∞';
            $this->vars['min'] = $min ?? null;
            $this->vars['maxStr'] = $max ?? '∞';
            $this->vars['max'] = $max ?? null;
        }
    }

    public function getActiveValue()
    {
        $data = json_decode(post('options'), true);
        $numbers = $this->numbersFromAjax($data['numbers'] ?? null);

        if (!empty($numbers)) {
            return $numbers;
        }

        return null;
    }

    public function applyScopeToQuery($query)
    {
        $scope = $this->filterScope;

        if (!is_array($scope->value) || count($scope->value) <= 1) {
            return;
        }

        list($min, $max) = array_values($scope->value);

        if (!isset($min) && !isset($max)) {
            return;
        }

        if ($scopeConditions = $scope->conditions) {
            [$sql, $bindings] = $this->processConditionBindings($scopeConditions, [
                'min' => $min === null ? -2147483647 : (float) $min,
                'max' => $max === null ? 2147483647 : (float) $max,
            ]);

            $query->whereRaw($sql, $bindings);
            return;
        }

        $scopeMethod = $scope->modelScope ?: $scope->scope;
        if ($scopeMethod) {
            $query->$scopeMethod($min, $max);
        }
    }

    protected function getNumericConfig(string $name)
    {
        $value = $this->filterScope->config[$name] ?? null;
        return is_numeric($value) ? $value : null;
    }

    protected function numbersFromAjax($ajaxNumbers): array
    {
        $numbers = [];

        if (empty($ajaxNumbers)) {
            return $numbers;
        }

        if (!is_array($ajaxNumbers)) {
            return is_numeric($ajaxNumbers) ? [(float) $ajaxNumbers] : [];
        }

        foreach ($ajaxNumbers as $number) {
            $numbers[] = is_numeric($number) ? (float) $number : null;
        }

        return $numbers;
    }
}
