<?php namespace Backend\FilterWidgets;

use Backend\Classes\FilterWidgetBase;

/**
 * Button group filter widget.
 */
class ButtonGroup extends FilterWidgetBase
{
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('buttongroup');
    }

    public function prepareVars()
    {
        $this->vars['scope'] = $this->filterScope;
        $this->vars['options'] = $this->getOptionsFromArray();
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
