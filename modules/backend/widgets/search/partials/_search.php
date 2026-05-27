<div class="search-widget-control">
    <div class="search-grow-group">
        <div class="loading-indicator-container size-input-text search-input-wrap">
            <input
                placeholder="<?= $placeholder ?>"
                type="text"
                name="<?= $this->getName() ?>"
                value="<?= e($value) ?>"
                data-request="<?= $this->getEventHandler('onSubmit') ?>"
                data-request-complete="var $wrap=$(this).closest('.search-widget-control'); if($(this).val().length) {$wrap.find('.clear-input-text').show()} else {$wrap.find('.clear-input-text').hide()};"
                <?= !$searchOnEnter ? 'data-track-input' : '' ?>
                data-load-indicator
                data-load-indicator-opaque
                class="form-control <?= $cssClasses ?>"
                autocomplete="off" />
            <button
                class="clear-input-text"
                type="button"
                value=""
                style="<?= empty($value) ? 'display: none;' : ''; ?>"
                onclick="var $wrap=$(this).closest('.search-widget-control'); var $input=$wrap.find('input[type=text]').first(); $input.val(''); $input.request();"
            >
                <i class="icon-times"></i>
            </button>
        </div>
        <a
            href="javascript:;"
            class="search-help-trigger search-help-button btn wn-icon-info"
            title="<?= e(trans('backend::lang.list.search_help_title')) ?>"
            aria-label="<?= e(trans('backend::lang.list.search_help_title')) ?>"
            data-control="popup"
            data-handler="<?= $this->getEventHandler('onLoadSearchHelp') ?>"></a>
    </div>
</div>
