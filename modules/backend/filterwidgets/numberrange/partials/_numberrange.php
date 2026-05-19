<a
    class="filter-scope-number filter-has-popover range <?= isset($min) || isset($max) ? 'active' : '' ?>"
    href="javascript:;"
    data-scope-name="<?= e($scope->scopeName) ?>"
    data-scope-data="<?= e(json_encode([
        'numbers' => [isset($min) ? $min : null, isset($max) ? $max : null],
        'step' => $step,
        'minValue' => $minValue,
        'maxValue' => $maxValue,
    ])) ?>">
    <span class="filter-label"><?= e(trans($scope->label)) ?>:</span>
    <span class="filter-setting"><?= isset($minStr) && isset($maxStr) ? ($minStr . ' → ' . $maxStr) : e(trans('backend::lang.filter.number_all')) ?></span>
</a>
