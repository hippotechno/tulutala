<?php namespace Backend\FilterWidgets;

use Backend\Classes\FilterWidgetBase;

/**
 * Checkbox filter widget.
 */
class Checkbox extends FilterWidgetBase
{
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('checkbox');
    }

    public function prepareVars()
    {
        $this->vars['scope'] = $this->filterScope;
    }

    public function getActiveValue()
    {
        return post('value') === true || post('value') === 'true';
    }

    public function applyScopeToQuery($query)
    {
        if (!$this->filterScope->value) {
            return;
        }

        $this->applyValueToQuery($query, true);
    }
}
