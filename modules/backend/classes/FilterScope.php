<?php namespace Backend\Classes;

use Winter\Storm\Html\Helper as HtmlHelper;

/**
 * Filter scope definition
 * A translation of the filter scope configuration
 *
 * @package winter\wn-backend-module
 * @author Alexey Bobkov, Samuel Georges
 */
class FilterScope
{
    /**
     * @var string Scope name.
     */
    public $scopeName;

    /**
     * @var string A prefix to the field identifier so it can be totally unique.
     */
    public $idPrefix;

    /**
     * @var string Column to display for the display name
     */
    public $nameFrom = 'name';

    /**
     * @var string Column to display for the description (optional)
     */
    public $descriptionFrom;

    /**
     * @var string Filter scope label.
     */
    public $label;

    /**
     * @var mixed Filter scope value.
     */
    public $value;

    /**
     * @var mixed Filter scope value used by filter widgets.
     */
    public $scopeValue;

    /**
     * @var string Model attribute to get/set value from.
     */
    public $valueFrom;

    /**
     * @var string Filter mode.
     */
    public $type = 'group';

    /**
     * @var string Filter options.
     */
    public $options;

    /**
     * @var string|array|mixed Default filter value.
     */
    public $default;

    /**
     * @var string Filter condition.
     */
    public $condition;

    /**
     * @var string|array Raw SQL conditions or condition map.
     */
    public $conditions;

    /**
     * @var string Model scope method to use when applying this filter scope.
     */
    public $modelScope;

    /**
     * @var string Model method used to retrieve filter options.
     */
    public $optionsMethod;

    /**
     * @var string Model scope method used to constrain option queries.
     */
    public $optionsScope;

    /**
     * @var string Empty option label.
     */
    public $emptyOption;

    /**
     * @var string Display style for active group scope values: title, key, or count.
     */
    public $displayValues = 'title';

    /**
     * @var string Group match mode: include, exclude, or toggle.
     */
    public $matchMode = 'include';

    /**
     * @var string Active group match mode.
     */
    public $mode = 'include';

    /**
     * @var mixed Number/date helper values for filter widgets.
     */
    public $min;
    public $max;
    public $after;
    public $before;
    public $valueRaw;
    public $afterRaw;
    public $beforeRaw;

    /**
     * @var string Date picker configuration.
     */
    public $minDate;
    public $maxDate;
    public $firstDay;
    public $yearRange;
    public $showWeekNumber = false;
    public $useTimezone;
    public $ignoreTimezone = false;

    /**
     * @var array Other scope names this scope depends on, when the other scopes are modified, this scope will update.
     */
    public $dependsOn;

    /**
     * @var string Specifies contextual visibility of this form scope.
     */
    public $context;

    /**
     * @var bool Specify if the scope is disabled or not.
     */
    public $disabled = false;

    /**
     * @var string Specifies a default value for supported scopes.
     */
    public $defaults;

    /**
     * @var string Model scope method to use when applying this filter scope.
     */
    public $scope;

    /**
     * @var string Specifies a CSS class to attach to the scope container.
     */
    public $cssClass;

    /**
     * @var array Raw scope configuration.
     */
    public $config;

    public function __construct($scopeName, $label)
    {
        $this->scopeName = $scopeName;
        $this->label = $label;
    }

    /**
     * Specifies a scope control rendering mode. Supported modes are:
     * - group - filter by a group of IDs. Default.
     * - checkbox - filter by a simple toggle switch.
     * @param string $type Specifies a render mode as described above
     * @param array $config A list of render mode specific config.
     */
    public function displayAs($type, $config = [])
    {
        $this->type = strtolower($type) ?: $this->type;
        $this->config = $this->evalConfig($config);
        return $this;
    }

    /**
     * Process options and apply them to this object.
     * @param array $config
     * @return array
     */
    protected function evalConfig($config)
    {
        if ($config === null) {
            $config = [];
        }

        /*
         * Standard config:property values
         */
        $applyConfigValues = [
            'options',
            'optionsMethod',
            'optionsScope',
            'emptyOption',
            'displayValues',
            'dependsOn',
            'context',
            'default',
            'valueFrom',
            'conditions',
            'condition',
            'modelScope',
            'scope',
            'cssClass',
            'nameFrom',
            'descriptionFrom',
            'disabled',
            'matchMode',
            'mode',
            'minDate',
            'maxDate',
            'firstDay',
            'yearRange',
            'showWeekNumber',
            'useTimezone',
            'ignoreTimezone',
        ];

        foreach ($applyConfigValues as $value) {
            if (array_key_exists($value, $config)) {
                $this->{$value} = $config[$value];
            }
        }

        return $config;
    }

    /**
     * Returns a value suitable for the scope id property.
     */
    public function getId($suffix = null)
    {
        $id = 'scope';
        $id .= '-'.$this->scopeName;

        if ($suffix) {
            $id .= '-'.$suffix;
        }

        if ($this->idPrefix) {
            $id = $this->idPrefix . '-' . $id;
        }

        return HtmlHelper::nameToId($id);
    }

    /**
     * Returns a HTML array compatible scope name.
     */
    public function getName()
    {
        return 'Filter[' . $this->scopeName . ']';
    }

    /**
     * Applies a loaded value to this scope and expands array values to properties.
     */
    public function setScopeValue($value)
    {
        $this->scopeValue = $value;
        $this->value = $value;

        if (is_array($value)) {
            foreach ($value as $key => $_value) {
                $this->{$key} = $_value;
            }
        }

        return $this;
    }
}
