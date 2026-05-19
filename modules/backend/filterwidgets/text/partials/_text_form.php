<div class="control-filter-popover control-filter-box-popover">
    <div class="filter-search loading-indicator-container size-input-text">
        <?php if (is_array($scope->conditions) && count($scope->conditions) > 1): ?>
            <select name="Filter[condition]" class="form-control custom-select select-no-search">
                <?php foreach ((array) $scope->conditions as $condition => $enabled): ?>
                    <?php if ($enabled): ?>
                        <option value="<?= e($condition) ?>" <?= $scope->condition === $condition ? 'selected="selected"' : '' ?>>
                            <?= e($this->getConditionLabel($condition)) ?>
                        </option>
                    <?php endif ?>
                <?php endforeach ?>
            </select>
        <?php else: ?>
            <?php foreach ((array) $scope->conditions as $condition => $enabled): ?>
                <input type="hidden" name="Filter[condition]" value="<?= e($condition) ?>">
            <?php endforeach ?>
        <?php endif ?>

        <input
            name="Filter[value]"
            value="<?= e($scope->value) ?>"
            class="form-control popup-allow-focus"
            autocomplete="off">
    </div>

    <div class="filter-buttons">
        <button class="btn btn-primary" data-filter-action="apply">Apply</button>
        <button class="btn btn-secondary" data-filter-action="clear">Clear</button>
    </div>
</div>
