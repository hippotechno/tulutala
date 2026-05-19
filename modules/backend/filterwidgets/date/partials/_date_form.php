<div class="control-filter-popover control-filter-box-popover --range">
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
            type="date"
            value="<?= e($scope->value) ?>"
            class="form-control popup-allow-focus"
            autocomplete="off">

        <div class="row" style="margin: 8px 0 0">
            <div class="col-xs-6" style="padding-left: 0">
                <input name="Filter[after]" type="date" value="<?= e($scope->after) ?>" class="form-control popup-allow-focus" autocomplete="off">
            </div>
            <div class="col-xs-6" style="padding-right: 0">
                <input name="Filter[before]" type="date" value="<?= e($scope->before) ?>" class="form-control popup-allow-focus" autocomplete="off">
            </div>
        </div>
    </div>

    <div class="filter-buttons">
        <button class="btn btn-primary" data-filter-action="apply">Apply</button>
        <button class="btn btn-secondary" data-filter-action="clear">Clear</button>
    </div>
</div>
