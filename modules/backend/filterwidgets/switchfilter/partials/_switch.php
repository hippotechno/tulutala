<div
    class="filter-scope checkbox custom-checkbox is-indeterminate"
    data-scope-name="<?= e($scope->scopeName) ?>">
    <input type="checkbox" id="<?= e($scope->getId()) ?>" data-checked="<?= e($scope->value ?: '0') ?>" />
    <label for="<?= e($scope->getId()) ?>"><?= e(trans($scope->label)) ?></label>
</div>
