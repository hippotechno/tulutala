<div class="modal-header">
    <button type="button" class="close" data-dismiss="popup">&times;</button>
    <h4 class="modal-title"><?= e(trans('backend::lang.list.search_help_title')) ?></h4>
</div>

<div class="modal-body">
    <p class="help-block before-field"><?= e(trans('backend::lang.list.search_help_intro')) ?></p>

    <div class="form-group">
        <label><?= e(trans('backend::lang.list.search_help_syntax_label')) ?></label>
        <ul class="text-muted" style="margin-bottom:0;padding-left:18px;">
            <li><strong><?= e(trans('backend::lang.list.search_help_syntax_keyword_label')) ?></strong> - <?= e(trans('backend::lang.list.search_help_syntax_keyword_desc')) ?> <code><?= e(trans('backend::lang.list.search_help_syntax_keyword')) ?></code></li>
            <li><strong><?= e(trans('backend::lang.list.search_help_syntax_field_label')) ?></strong> - <?= e(trans('backend::lang.list.search_help_syntax_field_desc')) ?> <code><?= e(trans('backend::lang.list.search_help_syntax_field')) ?></code></li>
            <li><strong><?= e(trans('backend::lang.list.search_help_syntax_phrase_label')) ?></strong> - <?= e(trans('backend::lang.list.search_help_syntax_phrase_desc')) ?> <code><?= e(trans('backend::lang.list.search_help_syntax_phrase')) ?></code></li>
            <li><strong><?= e(trans('backend::lang.list.search_help_syntax_multi_label')) ?></strong> - <?= e(trans('backend::lang.list.search_help_syntax_multi_desc')) ?> <code><?= e(trans('backend::lang.list.search_help_syntax_multi')) ?></code></li>
        </ul>
    </div>

    <div class="form-group">
        <label><?= e(trans('backend::lang.list.search_help_alias_label')) ?></label>
        <p class="help-block" style="margin:0;">
            <?= e(trans('backend::lang.list.search_help_alias_desc')) ?>
        </p>
    </div>

    <?php if (!empty($searchHelpFields)): ?>
        <div class="form-group">
            <label><?= e(trans('backend::lang.list.search_help_fields_label')) ?></label>
            <ul class="text-muted" style="margin-bottom:0;padding-left:18px;">
                <?php foreach ($searchHelpFields as $field): ?>
                    <li>
                        <?= e(trans('backend::lang.list.search_help_field_prefix')) ?> <code><?= e($field['alias']) ?></code>
                        <?php if (!empty($field['label']) && $field['label'] !== $field['alias']): ?>
                            - <?= e($field['label']) ?>
                        <?php endif ?>
                        <?php if (!empty($field['booleanValues'])): ?>
                            <div class="help-block" style="margin:4px 0 0 0;">
                                <?= e(trans('backend::lang.list.search_help_boolean_values')) ?>
                            </div>
                        <?php endif ?>
                        <?php if (!empty($field['aliases'])): ?>
                            <div class="help-block" style="margin:4px 0 0 0;">
                                <?= e(trans('backend::lang.list.search_help_aliases')) ?>:
                                <?= e(implode(', ', $field['aliases'])) ?>
                            </div>
                        <?php endif ?>
                    </li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif ?>

    <div class="form-group">
        <label><?= e(trans('backend::lang.list.search_help_examples_label')) ?></label>
        <ul class="text-muted" style="margin-bottom:0;padding-left:18px;">
            <li><?= e(trans('backend::lang.list.search_help_example_keyword_desc')) ?> <code><?= e(trans('backend::lang.list.search_help_example_keyword')) ?></code></li>
            <li><?= e(trans('backend::lang.list.search_help_example_field_desc')) ?> <code><?= e(trans('backend::lang.list.search_help_example_field')) ?></code></li>
            <li><?= e(trans('backend::lang.list.search_help_example_phrase_desc')) ?> <code><?= e(trans('backend::lang.list.search_help_example_phrase')) ?></code></li>
            <li><?= e(trans('backend::lang.list.search_help_example_multi_desc')) ?> <code><?= e(trans('backend::lang.list.search_help_example_multi')) ?></code></li>
            <li><?= e(trans('backend::lang.list.search_help_example_boolean_desc')) ?> <code><?= e(trans('backend::lang.list.search_help_example_boolean')) ?></code></li>
        </ul>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="popup">
        <?= e(trans('backend::lang.form.close')) ?>
    </button>
</div>
