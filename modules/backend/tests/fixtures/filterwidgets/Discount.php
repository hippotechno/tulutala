<?php namespace Backend\Tests\Fixtures\FilterWidgets;

use Backend\Classes\FilterWidgetBase;

class Discount extends FilterWidgetBase
{
    public function render()
    {
        $this->vars['scope'] = $this->filterScope;
        $this->vars['name'] = $this->getScopeName();
        $this->vars['value'] = $this->getLoadValue();

        return '<a href="javascript:;" class="filter-scope" data-scope-name="' . e($this->filterScope->scopeName) . '">' . e($this->filterScope->label) . '</a>';
    }

    public function renderForm()
    {
        return '<select name="Filter[value]"><option value="1">Yes</option></select>';
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
        if ($this->filterScope->value === '1') {
            $query->where('id', '>', 0);
        }
    }
}
