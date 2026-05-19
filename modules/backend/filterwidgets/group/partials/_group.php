<a
    href="javascript:;"
    class="filter-scope <?= $scope->scopeValue ? 'active' : '' ?>"
    data-scope-name="<?= e($scope->scopeName) ?>"
    <?php if ($depends = $this->getParentFilter()->getScopeDepends($scope)): ?>
        data-scope-depends="<?= $depends ?>"
    <?php endif ?>>
    <span class="filter-label"><?= e($this->getHeaderValue()) ?>:</span>
    <span class="filter-setting"><?= e($activeLabel) ?></span>
</a>
