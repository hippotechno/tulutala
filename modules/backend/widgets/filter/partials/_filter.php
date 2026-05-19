<div
    id="<?= $this->getId(); ?>"
    class="control-filter <?= $cssClasses ?>"
    data-control="filterwidget"
    data-custom-filter-widgets="<?= e(json_encode($this->getFilterWidgetScopeNames())) ?>"
    data-render-handler="<?= $this->getEventHandler('onFilterRenderForm') ?>"
    data-options-handler="<?= $this->getEventHandler('onFilterGetOptions') ?>"
    data-update-handler="<?= $this->getEventHandler('onFilterUpdate') ?>">

    <?= $this->makePartial('filter_scopes') ?>

</div>
