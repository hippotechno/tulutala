<a
    href="javascript:;"
    class="filter-scope <?= $scope->scopeValue ? 'active' : '' ?>"
    data-scope-name="<?= e($scope->scopeName) ?>">
    <span class="filter-label"><?= e($this->getHeaderValue()) ?>:</span>
    <span class="filter-setting"><?= $scope->scopeValue ? 1 : e(trans('backend::lang.filter.date_all')) ?></span>
</a>
