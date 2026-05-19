<a
    href="javascript:;"
    class="filter-scope <?= $this->hasActiveValue() ? 'active' : '' ?>"
    data-scope-name="<?= e($scope->scopeName) ?>">
    <span class="filter-label"><?= e($this->getHeaderValue()) ?>:</span>
    <span class="filter-setting"><?= e($this->getActiveLabel()) ?></span>
</a>
