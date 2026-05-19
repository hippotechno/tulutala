<div class="control-filter-popover control-filter-box-popover --range">
    <div class="filter-search loading-indicator-container size-input-text">
        <?php $enabledConditions = array_filter((array) $scope->conditions); ?>
        <?php if (count($enabledConditions) > 1): ?>
            <select name="Filter[condition]" class="form-control custom-select select-no-search">
                <?php foreach ($enabledConditions as $condition => $enabled): ?>
                    <option value="<?= e($condition) ?>" <?= $scope->condition === $condition ? 'selected="selected"' : '' ?>>
                        <?= e($this->getConditionLabel($condition)) ?>
                    </option>
                <?php endforeach ?>
            </select>
        <?php else: ?>
            <?php foreach ($enabledConditions as $condition => $enabled): ?>
                <input type="hidden" name="Filter[condition]" value="<?= e($condition) ?>">
            <?php endforeach ?>
        <?php endif ?>

        <input
            name="Filter[value]"
            type="number"
            value="<?= e($scope->value) ?>"
            class="form-control popup-allow-focus"
            autocomplete="off"
            placeholder="<?= e(trans('backend::lang.filter.number_all')) ?>">

        <?php if (isset($enabledConditions['between'])): ?>
            <div class="row" style="margin: 8px 0 0">
                <div class="col-xs-6" style="padding-left: 0">
                    <input name="Filter[min]" type="number" value="<?= e($scope->min) ?>" class="form-control popup-allow-focus" autocomplete="off" placeholder="Min">
                </div>
                <div class="col-xs-6" style="padding-right: 0">
                    <input name="Filter[max]" type="number" value="<?= e($scope->max) ?>" class="form-control popup-allow-focus" autocomplete="off" placeholder="Max">
                </div>
            </div>
        <?php endif ?>
    </div>

    <div class="filter-buttons">
        <button class="btn btn-primary" data-filter-action="apply">Apply</button>
        <button class="btn btn-secondary" data-filter-action="clear">Clear</button>
    </div>
</div>
