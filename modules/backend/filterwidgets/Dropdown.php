<?php namespace Backend\FilterWidgets;

use Backend\Classes\FilterWidgetBase;

/**
 * Dropdown filter widget.
 */
class Dropdown extends FilterWidgetBase
{
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('dropdown');
    }

    public function prepareVars()
    {
        $scope = $this->filterScope;
        $selectedValue = $scope->value ?? null;
        $options = $this->getOptionsFromArray();

        if (($scope->config['required'] ?? false) && $selectedValue === null && !empty($options)) {
            reset($options);
            $selectedValue = key($options);
        }

        $this->vars['scope'] = $scope;
        $this->vars['options'] = $options;
        $this->vars['selectedValue'] = $selectedValue;
    }

    public function getActiveValue()
    {
        $value = post('value');
        return $value === '' || $value === null ? null : $value;
    }

    public function applyScopeToQuery($query)
    {
        $this->applyValueToQuery($query);
    }
}
