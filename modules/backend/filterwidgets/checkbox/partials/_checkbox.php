<div
    class="filter-scope checkbox custom-checkbox"
    data-scope-name="<?= e($scope->scopeName) ?>">
    <input type="checkbox" id="<?= e($scope->getId()) ?>" <?= $scope->value ? 'checked' : '' ?> />
    <label for="<?= e($scope->getId()) ?>"><?= e(trans($scope->label)) ?></label>
</div>
