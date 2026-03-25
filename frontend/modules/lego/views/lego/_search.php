<?php

use common\models\Set;
use common\widgets\InlineScript;
use frontend\components\T;
use frontend\models\searches\SetSearch;
use yii\helpers\Html;
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
                        ->dropDownList(Set::getAvailableThemesList(), ['prompt' => T::tr('Any theme'), 'data-placeholder' => 'Any theme'])
                        ->label(false) ?>
            </div>

            <div class="col-md-3 mb-3 mb-lg-0">
                <?= $form->field($model, 'sort_option')
                        ->dropDownList(SetSearch::getSortOptions(), ['prompt' => T::tr('Sort by'), 'data-placeholder' => 'Sort by'])
                        ->label(false) ?>
            </div>

            <div class="col-md-2 mb-3 mb-lg-0">
                <?= $form->field($model, 'year')
                        ->dropDownList(Set::getAvailableYearsList(), ['prompt' => T::tr('Any year'), 'data-placeholder' => 'Any year'])
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
                $select.select2({
                    theme: "bootstrap-5",
                    placeholder: {
                        id: '-1',
                        text: $select.data('placeholder'),
                    }
                });
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