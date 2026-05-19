<?php
$required = $scope->config['required'] ?? false;
$emptyOption = $scope->config['emptyOption'] ?? $scope->label ?? Lang::get('backend::lang.form.select_placeholder');
$hasEmpty = !$required && $emptyOption;
?>
<div class="filter-scope dropdown" data-scope-name="<?= e($scope->scopeName) ?>">
    <select
        class="form-control custom-select select-no-search"
        data-placeholder="<?= e(trans($emptyOption)); ?>"
        data-dropdown-auto-width="true"
        data-width="resolve"
        <?= $required ? 'data-allow-clear="false"' : ''; ?>
        name="<?= e($scope->scopeName) ?>"
    >
        <?php if ($hasEmpty): ?>
            <option value="" <?= $selectedValue === null || $selectedValue === '' ? 'selected' : '' ?>></option>
        <?php endif; ?>
        <?php foreach ($options as $key => $label): ?>
            <option value="<?= e($key) ?>" <?= $selectedValue == $key ? 'selected' : '' ?>>
                <?= e(trans($label)) ?>
            </option>
        <?php endforeach ?>
    </select>
</div>
