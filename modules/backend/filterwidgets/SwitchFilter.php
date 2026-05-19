<?php namespace Backend\FilterWidgets;

use Backend\Classes\FilterWidgetBase;

/**
 * Switch filter widget.
 */
class SwitchFilter extends FilterWidgetBase
{
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('switch');
    }

    public function prepareVars()
    {
        $this->vars['scope'] = $this->filterScope;
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
