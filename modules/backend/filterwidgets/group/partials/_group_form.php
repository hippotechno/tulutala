<div id="controlFilterPopover" class="control-filter-popover control-filter-box-popover --range">
    <div class="filter-search loading-indicator-container size-input-text">
        <button class="close" data-dismiss="popover" type="button">&times;</button>
        <input
            type="text"
            name="search"
            autocomplete="off"
            class="filter-search-input form-control icon search popup-allow-focus"
            data-search />

        <?php if ($scope->matchMode === 'toggle'): ?>
            <select name="Filter[mode]" class="form-control custom-select select-no-search">
                <option value="include" <?= $scope->mode !== 'exclude' ? 'selected="selected"' : '' ?>>Includes</option>
                <option value="exclude" <?= $scope->mode === 'exclude' ? 'selected="selected"' : '' ?>>Excludes</option>
            </select>
        <?php else: ?>
            <input type="hidden" name="Filter[mode]" value="<?= e($scope->matchMode ?: 'include') ?>">
        <?php endif ?>

        <?php
            $activeValues = array_map('strval', array_keys((array) $scope->value));
            $availableOptions = array_filter($options, function ($value) use ($activeValues) {
                return !in_array((string) $value, $activeValues, true);
            }, ARRAY_FILTER_USE_KEY);
            $activeOptions = array_intersect_key($options, array_flip($activeValues));
        ?>

        <div class="filter-items">
            <ul>
                <?php foreach ($availableOptions as $value => $label): ?>
                    <li data-item-id="<?= e($value) ?>">
                        <a href="javascript:;"><?= e(trans($label)) ?></a>
                    </li>
                <?php endforeach ?>
            </ul>
        </div>

        <div class="filter-active-items">
            <ul>
                <?php foreach ($activeOptions as $value => $label): ?>
                    <li data-item-id="<?= e($value) ?>">
                        <a href="javascript:;"><?= e(trans($label)) ?></a>
                    </li>
                <?php endforeach ?>
            </ul>
        </div>
    </div>

    <div class="filter-buttons">
        <button class="btn btn-block btn-primary wn-icon-filter" data-filter-action="apply">
            Apply
        </button>
        <button class="btn btn-block btn-secondary wn-icon-eraser" data-filter-action="clear">
            Clear
        </button>
    </div>
</div>
