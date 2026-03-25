<?php

use common\widgets\InlineScript;
use frontend\components\T;
use frontend\models\searches\SetSearch;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/**
 * @var View       $this
 * @var SetSearch  $model
 * @var ActiveForm $form
 */

?>

    <div class="set-search">

        <?php $form = ActiveForm::begin([
                'action'  => ['/lego'],
                'method'  => 'get',
                'options' => ['id' => 'set-search-form'],
        ]); ?>

        <div class="row">
            <div class="col-12 col-md-4 mb-3 mb-lg-0">
                <div class="row g-2">
                    <div class="col-10 col-lg-12">
                        <?= $form->field($model, 'name')->label(false) ?>
                    </div>
                    <div class="col-2 d-lg-none" style="text-align: right;">
                        <?= Html::submitButton(Html::tag('i', '', ['class' => 'bi bi-search']), ['class' => 'btn btn-primary w-100']) ?>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3 mb-lg-0">
                <?= $form->field($model, 'theme_id')
                        ->dropDownList($model->theme_id ? [(int)$model->theme_id => $model->theme->name] : [], [
                                'prompt'           => T::tr('Any theme'),
                                'data-placeholder' => T::tr('Any theme'),
                                'data-ajax-url'    => Url::to(['/autocomplete/theme']),
                        ])
                        ->label(false) ?>
            </div>

            <div class="col-md-3 mb-3 mb-lg-0">
                <?= $form->field($model, 'sort_option')
                        ->dropDownList(SetSearch::getSortOptions(), [
                                'prompt'           => T::tr('Sort by'),
                                'data-placeholder' => T::tr('Sort by'),
                        ])
                        ->label(false) ?>
            </div>

            <div class="col-md-2 mb-3 mb-lg-0">
                <?= $form->field($model, 'year')
                        ->dropDownList($model->year ? [(int)$model->year => $model->year] : [], [
                                'prompt'           => T::tr('Any year'),
                                'data-placeholder' => T::tr('Any year'),
                                'data-ajax-url'    => Url::to(['/autocomplete/year']),
                        ])
                        ->label(false) ?>
            </div>
        </div>

        <div class="form-group d-none">
            <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
            <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>


<?php InlineScript::begin(); ?>
    <script>
        (() => {

            const $selects = $('form#set-search-form select');
            $selects.each(function () {
                const $select = $(this);
                const ajaxUrl = $select.data('ajax-url');

                const select2Config = {
                    theme: "bootstrap-5",
                    width: $select.data('width') ? $select.data('width') : $select.hasClass('w-100') ? '100%' : 'style',
                    placeholder: $select.data('placeholder'),
                    allowClear: true,
                };

                if (ajaxUrl) {
                    select2Config.ajax = {
                        url: ajaxUrl,
                        dataType: 'json',
                        delay: 250,
                        cache: true,
                        data: (params) => ({
                            term: params.term || '',
                            page: params.page || 1,
                        }),
                        processResults: (data) => ({
                            results: data.results || [],
                            pagination: {
                                more: Boolean(data.pagination && data.pagination.more),
                            },
                        }),
                    };
                }

                $select.select2(select2Config);
            });

            const searchForm = document.getElementById('set-search-form');
            if (!searchForm) {
                return;
            }

            $selects.on('change', () => {
                searchForm.submit();
            });

            const themeSearch = document.getElementById('theme-select-search');
            const themeSelect = document.getElementById('theme_id');
            if (!themeSearch || !themeSelect) {
                return;
            }

            themeSearch.addEventListener('input', () => {
                const query = themeSearch.value.trim().toLowerCase();
                Array.from(themeSelect.options).forEach((option) => {
                    if (option.value === '') {
                        option.hidden = false;
                        return;
                    }

                    option.hidden = query !== '' && !option.text.toLowerCase().includes(query);
                });
            });
        })();
    </script>
<?php InlineScript::end(); ?>