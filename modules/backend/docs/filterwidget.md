# Backend Filter Widgets

Filter widgets are reusable controls for `Backend\Widgets\Filter` scopes. They let a filter scope render custom UI, capture posted values, and apply its own query logic while still using the normal WinterCMS filter session, `filter.update` event, and list refresh flow.

This feature is similar in shape to form widgets:

- Register a widget class with `WidgetManager`.
- Use its short alias as the scope `type` in `config_filter.yaml`.
- Put widget PHP classes under a lowercase `filterwidgets` folder.
- Put widget partials under a lowercase partial folder.

## Built-In Widgets

The backend module registers these filter widget aliases:

| Alias | Class | Purpose |
| --- | --- | --- |
| `checkbox` | `Backend\FilterWidgets\Checkbox` | Simple boolean toggle. |
| `switch` | `Backend\FilterWidgets\SwitchFilter` | Indeterminate switch-style toggle using the legacy switch payload. |
| `dropdown` | `Backend\FilterWidgets\Dropdown` | Single-select option filter. |
| `button-group` | `Backend\FilterWidgets\ButtonGroup` | Inline button option filter. |
| `text` | `Backend\FilterWidgets\Text` | Single text input with equals or contains matching. |
| `number` | `Backend\FilterWidgets\Number` | Single numeric input by default, with optional numeric conditions. |
| `numberrange` | `Backend\FilterWidgets\NumberRange` | Legacy-style numeric range popover. |
| `date` | `Backend\FilterWidgets\Date` | Date input with optional date conditions. |
| `daterange` | `Backend\FilterWidgets\DateRange` | Legacy-style date range popover. |
| `group` | `Backend\FilterWidgets\Group` | Legacy-style selectable group with model, array, or language-key options. |

The base filter widget no longer renders scope-specific partials directly. Every scope type must resolve to a registered filter widget alias; missing aliases throw an exception during render.

## Basic Usage

Define scopes in a filter config file and set `type` to a registered widget alias.

```yaml
# plugins/acme/demo/controllers/items/config_filter.yaml
scopes:
    name:
        label: acme.demo::lang.item.name
        type: text
        conditions:
            contains: true
        scope: filterName

    seats:
        label: acme.demo::lang.item.seats
        type: number
        min: 1
        max: 8
        scope: filterSeats

    published_at:
        label: acme.demo::lang.item.published_at
        type: date
        condition: equals
        scope: filterPublishedAt

    category:
        label: acme.demo::lang.item.category
        type: group
        modelClass: Acme\Demo\Models\Category
        nameFrom: name
        scope: filterCategory
```

The `label` value is passed through `trans()`, so language keys are supported and expected.

## Model Scopes

When a scope defines `scope` or `modelScope`, the filter widget calls that method on the query. For `text`, `number`, `date`, `dropdown`, `button-group`, `checkbox`, and `switch`, the active scalar value is passed. For `group`, the selected keys are passed as an array. For `daterange` and `numberrange`, the range endpoints are passed as separate arguments.

```php
public function scopeFilterSeats($query, $value)
{
    return $query->where('seats', '>=', (int) $value);
}

public function scopeFilterCategory($query, array $ids)
{
    return $query->whereIn('category_id', $ids);
}
```

If no model scope is configured, the built-in widgets fall back to applying query constraints against `valueFrom` or the scope name.

```yaml
scopes:
    seats:
        label: acme.demo::lang.item.seats
        type: number
        valueFrom: seats
```

## Conditions

`conditions` can be a boolean map of built-in condition names or a raw SQL condition string.

```yaml
scopes:
    seats:
        label: acme.demo::lang.item.seats
        type: number
        conditions:
            equals: true
            greater: true
            lesser: true
            between: true
```

For raw SQL conditions, named placeholders are converted to bound query parameters where supported by the widget.

```yaml
scopes:
    seats:
        label: acme.demo::lang.item.seats
        type: number
        conditions:
            equals: "seats = :value"
```

Common placeholders:

| Widget | Placeholders |
| --- | --- |
| `text` | `:value`, `:filtered` |
| `number` | `:value`, `:filtered`, `:min`, `:max` |
| `numberrange` | `:min`, `:max` |
| `date` | `:value`, `:filtered`, `:valueDate`, `:after`, `:afterDate`, `:before`, `:beforeDate` |
| `daterange` | `:after`, `:afterDate`, `:before`, `:beforeDate` |
| `group`, `dropdown`, `button-group`, `checkbox`, `switch` | `:value`, `:filtered` |

## Number Widget

The `number` widget defaults to a single numeric input and a single `equals` condition. This keeps a normal `type: number` scope from rendering as a dropdown or condition selector unless conditions are explicitly enabled.

```yaml
scopes:
    no_players:
        label: hippo.bg360::lang.reportwidget.bgrooms.filter.slots
        scope: availableSlotQty
        type: number
        step: 1
        min: 1
        max: 8
```

The active filter header shows the selected number, not a count. Examples: `3`, `>= 3`, `<= 8`, or `1 - 8`.

## Group Widget

The `group` widget supports three option sources.

### Model Options

If `modelClass` is configured, the widget builds options from that model and uses `nameFrom` for labels.

```yaml
scopes:
    state:
        label: acme.demo::lang.item.state
        type: group
        modelClass: Acme\Demo\Models\State
        nameFrom: name
        scope: byState
```

### Options Method

If `options` or `optionsMethod` names a model method, it is called on the configured scope model. The current filter scopes are passed to the method so dependent filters can inspect active values.

```yaml
scopes:
    city:
        label: acme.demo::lang.item.city
        type: group
        modelClass: Acme\Demo\Models\Room
        options: getCityFilterOptions
        dependsOn: state
        scope: byCity
```

```php
public function getCityFilterOptions($scopes = null): array
{
    $stateIds = array_keys((array) ($scopes['state']->value ?? []));

    return City::query()
        ->when($stateIds, fn ($query) => $query->whereIn('state_id', $stateIds))
        ->pluck('name', 'id')
        ->all();
}
```

### Array Or Language Key Options

```yaml
scopes:
    status:
        label: acme.demo::lang.item.status
        type: group
        options:
            draft: acme.demo::lang.status.draft
            published: acme.demo::lang.status.published
```

The option labels are passed through `trans()`.

### Active Header Display

`displayValues` controls what the active `group` header displays.

```yaml
scopes:
    city:
        label: acme.demo::lang.item.city
        type: group
        displayValues: title
```

Supported values:

| Value | Header display |
| --- | --- |
| `title` | Selected option titles. This is the default. |
| `key` | Selected keys or IDs. |
| `count` | Number of selected values. |

## Registering A Plugin Filter Widget

Add `registerFilterWidgets()` to the plugin registration class.

```php
<?php namespace Acme\Demo;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function registerFilterWidgets()
    {
        return [
            \Acme\Demo\FilterWidgets\Rating::class => 'rating',
        ];
    }
}
```

Then use the alias in YAML:

```yaml
scopes:
    rating:
        label: acme.demo::lang.item.rating
        type: rating
        scope: filterRating
```

## Creating A Custom Widget

Create the widget class:

```php
<?php namespace Acme\Demo\FilterWidgets;

use Backend\Classes\FilterWidgetBase;

class Rating extends FilterWidgetBase
{
    public function render()
    {
        $this->vars['scope'] = $this->getFilterScope();
        return $this->makePartial('rating');
    }

    public function renderForm()
    {
        $this->vars['scope'] = $this->getFilterScope();
        return $this->makePartial('rating_form');
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
        if ($this->applyModelScopeToQuery($query)) {
            return;
        }

        $query->where($this->valueFrom, $this->getFilterScope()->value);
    }
}
```

Render the filter scope header:

```php
<!-- plugins/acme/demo/filterwidgets/rating/partials/_rating.php -->
<a
    href="javascript:;"
    class="filter-scope <?= $scope->scopeValue ? 'active' : '' ?>"
    data-scope-name="<?= e($scope->scopeName) ?>">
    <span class="filter-label"><?= e($this->getHeaderValue()) ?>:</span>
    <span class="filter-setting">
        <?= $scope->scopeValue ? e($scope->value) : e(trans('backend::lang.filter.all')) ?>
    </span>
</a>
```

Render the popup form:

```php
<!-- plugins/acme/demo/filterwidgets/rating/partials/_rating_form.php -->
<div class="control-filter-popover control-filter-box-popover">
    <div class="filter-search loading-indicator-container size-input-text">
        <input
            name="Filter[value]"
            type="number"
            value="<?= e($scope->value) ?>"
            class="form-control popup-allow-focus"
            autocomplete="off">
    </div>

    <div class="filter-buttons">
        <button class="btn btn-primary" data-filter-action="apply">Apply</button>
        <button class="btn btn-secondary" data-filter-action="clear">Clear</button>
    </div>
</div>
```

Important markup requirements:

- Header links must use `class="filter-scope"`.
- Header links must include `data-scope-name="<?= e($scope->scopeName) ?>"`.
- Popup inputs should use the `Filter[...]` name prefix.
- Popup apply and clear buttons should use `data-filter-action="apply"` and `data-filter-action="clear"`.

## Runtime Flow

1. `Backend\Widgets\Filter` reads `config_filter.yaml` and builds `FilterScope` objects.
2. If a scope `type` resolves to a registered filter widget alias, the scope is rendered by that widget.
3. Clicking the scope calls `onFilterRenderForm` and renders `renderForm()` inside a popover.
4. Clicking Apply posts the popup inputs to `onFilterUpdate`.
5. The filter widget's `getActiveValue()` normalizes the posted data.
6. `FilterScope::setScopeValue()` stores the value in session and expands array keys like `value`, `condition`, `min`, `max`, `mode`, `after`, and `before` onto the scope object.
7. `filter.update` fires. List and relation controllers listen to this event and refresh their list widgets.
8. When the list query is rebuilt, `applyAllScopesToQuery()` calls the widget's `applyScopeToQuery()`.

## Manual Filter Widgets In Report Widgets

If a report widget manually creates a `Backend\Widgets\Filter` and a list widget, attach the filter to the list with `addFilter()`. The list widget links itself back to the filter, so filter widgets can fall back to the attached list widget model when `filterConfig->model` is not set.

```php
$listConfig = $this->makeConfig('$/acme/demo/reportwidgets/items/config_list.yaml');
$mainConfig = $this->makeConfig($listConfig->list);
$mainConfig->model = new $listConfig->modelClass;

$listWidget = $this->makeWidget(\Backend\Widgets\Lists::class, $mainConfig);

$filterConfig = $this->makeConfig($listConfig->filter);
$filterConfig->alias = $listWidget->alias . 'Filter';

$filterWidget = $this->makeWidget(\Backend\Widgets\Filter::class, $filterConfig);
$filterWidget->bindToController();

$filterWidget->bindEvent('filter.update', function () use ($listWidget) {
    return $listWidget->onFilter();
});

$listWidget->addFilter([$filterWidget, 'applyAllScopesToQuery']);
```

Set `$filterConfig->model` only when the filter should use a different model than the attached list widget.

## Notes And Debugging

- If a filter only applies after a full page reload, verify the filter widget is bound to a list refresh through the `filter.update` event.
- If an options method is called on the widget class instead of the model, pass `model` to the filter widget config or set `modelClass` on the scope.
- If a built-in type such as `number` renders as a dropdown, confirm the filter widget alias is registered and that `data-custom-filter-widgets` includes the scope name in the rendered filter container.
- If a custom popover throws JavaScript errors from the legacy group search input, confirm the scope is detected as a custom filter widget and not handled by the legacy group popover path.
- Use language keys for `label`; filter widget headers translate them through `trans()`.
- Prefer model scopes for business logic. Use raw SQL `conditions` only when the condition is local to the filter and does not need model-level reuse.
