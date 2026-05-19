<?php

namespace Backend\Tests\Classes;

use System\Tests\Bootstrap\TestCase;
use Backend\FilterWidgets\Group;
use Backend\FilterWidgets\Number;
use Backend\FilterWidgets\Dropdown;
use Backend\FilterWidgets\Checkbox;
use Backend\FilterWidgets\DateRange;
use Backend\FilterWidgets\NumberRange;
use Backend\Classes\WidgetManager;
use Backend\Tests\Fixtures\FilterWidgets\Discount;
use Backend\Widgets\Filter;
use Backend\Widgets\Lists;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Request;

class WidgetManagerTest extends TestCase
{
    public function testListFormWidgets()
    {
        $manager = WidgetManager::instance();
        $widgets = $manager->listFormWidgets();

        $this->assertArrayHasKey('TestVendor\Test\FormWidgets\Sample', $widgets);
        $this->assertArrayHasKey('Winter\Tester\FormWidgets\Preview', $widgets);
    }

    public function testListFilterWidgets()
    {
        $manager = WidgetManager::instance();
        $manager->registerFilterWidget(Discount::class, 'discount');

        $widgets = $manager->listFilterWidgets();

        $this->assertArrayHasKey(Discount::class, $widgets);
        $this->assertSame(Discount::class, $manager->resolveFilterWidget('discount'));
    }

    public function testCustomFilterWidgetCanRenderAndRenderForm()
    {
        WidgetManager::instance()->registerFilterWidget(Discount::class, 'discount');

        $filter = $this->createFilterWithScope('discount', [
            'type' => 'discount',
            'label' => 'Discount',
        ]);

        $html = $filter->render();
        $this->assertStringContainsString('data-scope-name="discount"', $html);
        $this->assertSame(['discount'], $filter->getFilterWidgetScopeNames());

        Request::swap(HttpRequest::create('/', 'POST', ['scopeName' => 'discount']));
        $result = $filter->onFilterRenderForm();

        $this->assertSame('discount', $result['scopeName']);
        $this->assertStringContainsString('name="Filter[value]"', $result['html']);
    }

    public function testCustomFilterWidgetCanCaptureValueAndApplyQuery()
    {
        WidgetManager::instance()->registerFilterWidget(Discount::class, 'discount');

        $filter = $this->createFilterWithScope('discount', [
            'type' => 'discount',
            'label' => 'Discount',
        ]);
        $filter->render();

        Request::swap(HttpRequest::create('/', 'POST', [
            'scopeName' => 'discount',
            'Filter' => ['value' => '1'],
        ]));

        $filter->onFilterUpdate();

        $scope = $filter->getScope('discount');
        $this->assertSame('1', $scope->value);
        $this->assertSame(['value' => '1'], $scope->scopeValue);

        $query = new class {
            public $wheres = [];

            public function where($column, $operator, $value)
            {
                $this->wheres[] = [$column, $operator, $value];
            }
        };

        $filter->applyScopeToQuery($scope, $query);

        $this->assertSame([['id', '>', 0]], $query->wheres);
    }

    public function testNumberFilterWidgetCanCaptureScalarValueAndApplyModelScope()
    {
        WidgetManager::instance()->registerFilterWidget(Number::class, 'number');

        $filter = $this->createFilterWithScope('slots', [
            'type' => 'number',
            'label' => 'Slots',
            'scope' => 'availableSlotQty',
        ]);
        $filter->render();

        Request::swap(HttpRequest::create('/', 'POST', [
            'scopeName' => 'slots',
            'Filter' => [
                'condition' => 'equals',
                'value' => '3',
            ],
        ]));

        $result = $filter->onFilterUpdate();

        $scope = $filter->getScope('slots');
        $this->assertSame('3', $scope->value);
        $this->assertSame(['condition' => 'equals', 'value' => '3'], $scope->scopeValue);
        $this->assertSame('slots', $result['scopeName']);
        $this->assertTrue($result['scopeIsActive']);
        $this->assertSame('3', $result['scopeActiveLabel']);

        $query = new class {
            public $scopes = [];

            public function availableSlotQty($value)
            {
                $this->scopes[] = ['availableSlotQty', $value];
            }
        };

        $filter->applyScopeToQuery($scope, $query);

        $this->assertSame([['availableSlotQty', '3']], $query->scopes);
    }

    public function testInlineFilterWidgetsCanCaptureValuesAndApplyModelScope()
    {
        WidgetManager::instance()->registerFilterWidget(Dropdown::class, 'dropdown');
        WidgetManager::instance()->registerFilterWidget(Checkbox::class, 'checkbox');

        $filter = $this->createFilterWithScope('status', [
            'type' => 'dropdown',
            'label' => 'Status',
            'scope' => 'filterStatus',
            'options' => [
                'open' => 'Open',
                'closed' => 'Closed',
            ],
        ]);
        $filter->addScopes([
            'visible' => [
                'type' => 'checkbox',
                'label' => 'Visible',
                'scope' => 'filterVisible',
            ],
        ]);
        $html = $filter->render();

        $this->assertStringContainsString('class="filter-scope dropdown"', $html);
        $this->assertStringContainsString('class="filter-scope checkbox custom-checkbox"', $html);

        Request::swap(HttpRequest::create('/', 'POST', [
            'scopeName' => 'status',
            'value' => 'open',
        ]));
        $filter->onFilterUpdate();

        Request::swap(HttpRequest::create('/', 'POST', [
            'scopeName' => 'visible',
            'value' => 'true',
        ]));
        $filter->onFilterUpdate();

        $query = new class {
            public $scopes = [];

            public function filterStatus($value)
            {
                $this->scopes[] = ['filterStatus', $value];
            }

            public function filterVisible($value)
            {
                $this->scopes[] = ['filterVisible', $value];
            }
        };

        $filter->applyAllScopesToQuery($query);

        $this->assertContains(['filterStatus', 'open'], $query->scopes);
        $this->assertContains(['filterVisible', true], $query->scopes);
    }

    public function testRangeFilterWidgetsCanCaptureValuesAndApplyModelScope()
    {
        WidgetManager::instance()->registerFilterWidget(DateRange::class, 'daterange');
        WidgetManager::instance()->registerFilterWidget(NumberRange::class, 'numberrange');

        $filter = $this->createFilterWithScope('created_at', [
            'type' => 'daterange',
            'label' => 'Created',
            'scope' => 'createdBetween',
        ]);
        $filter->addScopes([
            'price' => [
                'type' => 'numberrange',
                'label' => 'Price',
                'scope' => 'priceBetween',
            ],
        ]);
        $filter->render();

        Request::swap(HttpRequest::create('/', 'POST', [
            'scopeName' => 'created_at',
            'options' => json_encode([
                'dates' => ['2026-05-01 00:00:00', '2026-05-19 23:59:59'],
            ]),
        ]));
        $filter->onFilterUpdate();

        Request::swap(HttpRequest::create('/', 'POST', [
            'scopeName' => 'price',
            'options' => json_encode([
                'numbers' => ['10', '20'],
            ]),
        ]));
        $filter->onFilterUpdate();

        $query = new class {
            public $scopes = [];

            public function createdBetween($after, $before)
            {
                $this->scopes[] = ['createdBetween', $after->format('Y-m-d H:i:s'), $before->format('Y-m-d H:i:s')];
            }

            public function priceBetween($min, $max)
            {
                $this->scopes[] = ['priceBetween', $min, $max];
            }
        };

        $filter->applyAllScopesToQuery($query);

        $this->assertContains(['createdBetween', '2026-05-01 00:00:00', '2026-05-19 23:59:59'], $query->scopes);
        $this->assertContains(['priceBetween', 10.0, 20.0], $query->scopes);
    }

    public function testUpdatingScopeClearsDependentScopeSessionValues()
    {
        WidgetManager::instance()->registerFilterWidget(Group::class, 'group');

        $filter = $this->createFilterWithScope('state', [
            'type' => 'group',
            'label' => 'State',
            'options' => [
                1 => 'State 1',
            ],
        ]);
        $filter->addScopes([
            'city' => [
                'type' => 'group',
                'label' => 'City',
                'dependsOn' => 'state',
                'options' => [
                    10 => 'City 10',
                ],
            ],
        ]);
        $filter->render();
        $filter->setScopeValue('city', [
            'value' => [10 => 10],
            'mode' => 'include',
        ]);

        Request::swap(HttpRequest::create('/', 'POST', [
            'scopeName' => 'state',
            'clearScope' => 1,
        ]));

        $filter->onFilterUpdate();

        $this->assertNull($filter->getScope('state')->scopeValue);
        $this->assertNull($filter->getScope('city')->scopeValue);
        $this->assertNull($filter->getScopeValue('city'));
    }

    public function testGroupFilterWidgetCanApplyExcludeMode()
    {
        WidgetManager::instance()->registerFilterWidget(Group::class, 'group');

        $filter = $this->createFilterWithScope('state', [
            'type' => 'group',
            'label' => 'State',
            'matchMode' => 'exclude',
            'options' => [
                1 => 'State 1',
                2 => 'State 2',
            ],
        ]);
        $filter->render();

        Request::swap(HttpRequest::create('/', 'POST', [
            'scopeName' => 'state',
            'Filter' => [
                'mode' => 'include',
                'value' => [1, 2],
            ],
        ]));
        $filter->onFilterUpdate();

        $scope = $filter->getScope('state');
        $this->assertSame('exclude', $scope->mode);

        $query = new class {
            public $whereNotIns = [];

            public function whereNotIn($column, $values)
            {
                $this->whereNotIns[] = [$column, $values];
            }
        };

        $filter->applyScopeToQuery($scope, $query);

        $this->assertSame([
            ['state', [1, 2]],
        ], $query->whereNotIns);
    }

    public function testGroupFilterWidgetToggleModeRendersAndPassesModeToModelScope()
    {
        WidgetManager::instance()->registerFilterWidget(Group::class, 'group');

        $filter = $this->createFilterWithScope('state', [
            'type' => 'group',
            'label' => 'State',
            'matchMode' => 'toggle',
            'scope' => 'filterState',
            'options' => [
                1 => 'State 1',
            ],
        ]);
        $filter->render();

        Request::swap(HttpRequest::create('/', 'POST', ['scopeName' => 'state']));
        $result = $filter->onFilterRenderForm();
        $this->assertStringContainsString('value="include"', $result['html']);
        $this->assertStringContainsString('value="exclude"', $result['html']);

        Request::swap(HttpRequest::create('/', 'POST', [
            'scopeName' => 'state',
            'Filter' => [
                'mode' => 'exclude',
                'value' => [1],
            ],
        ]));
        $filter->onFilterUpdate();

        $query = new class {
            public $scopes = [];

            public function filterState($values, $mode)
            {
                $this->scopes[] = ['filterState', $values, $mode];
            }
        };

        $filter->applyScopeToQuery('state', $query);

        $this->assertSame([
            ['filterState', [1], 'exclude'],
        ], $query->scopes);
    }

    public function testFilterWidgetCanResolveModelFromAttachedListWidget()
    {
        $model = new class extends \Winter\Storm\Database\Model {
            protected $table = 'backend_test_filter_widget_models';
        };

        $filter = $this->createFilterWithScope('discount', [
            'type' => 'discount',
            'label' => 'Discount',
        ]);

        $list = new Lists(null, [
            'columns' => [],
            'model' => $model,
        ]);
        $list->addFilter([$filter, 'applyAllScopesToQuery']);

        $this->assertSame($model, $filter->getModel());
    }

    public function testIfWidgetsCanBeExtended()
    {
        $manager = WidgetManager::instance();
        $manager->registerReportWidget('Acme\Fake\ReportWidget\HelloWorld', [
            'name' => 'Hello World Test',
            'context' => 'dashboard'
        ]);
        $widgets = $manager->listReportWidgets();

        $this->assertArrayHasKey('Acme\Fake\ReportWidget\HelloWorld', $widgets);
    }

    public function testIfWidgetsCanBeRemoved()
    {
        $manager = WidgetManager::instance();
        $manager->registerReportWidget('Acme\Fake\ReportWidget\HelloWorld', [
            'name' => 'Hello World Test',
            'context' => 'dashboard'
        ]);
        $manager->registerReportWidget('Acme\Fake\ReportWidget\ByeWorld', [
            'name' => 'Hello World Bye',
            'context' => 'dashboard'
        ]);

        $manager->removeReportWidget('Acme\Fake\ReportWidget\ByeWorld');

        $widgets = $manager->listReportWidgets();

        $this->assertCount(1, $widgets);
    }

    protected function createFilterWithScope(string $name, array $scopeConfig): Filter
    {
        return new Filter(null, [
            'arrayName' => 'array',
            'scopes' => [$name => $scopeConfig],
        ]);
    }
}
