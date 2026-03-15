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
            <div class="col-9 col-md-4 mb-3 mb-lg-0">
                <?= $form->field($model, 'name')
                        ->label(false) ?>
            </div>

            <div class="col-3 col-md-auto d-lg-none mb-3 mb-lg-0" style="text-align: right;">
                <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
            </div>

            <div class="col-md-3 mb-3 mb-lg-0">
                <?= $form->field($model, 'theme_id')
                        ->dropDownList(Set::getAvailableThemesList(), ['prompt' => T::tr('Any theme')])
                        ->label(false) ?>
            </div>

            <div class="col-md-3 mb-3 mb-lg-0">
                <?= $form->field($model, 'sort_option')
                        ->dropDownList(SetSearch::getSortOptions(), ['prompt' => T::tr('Sort by')])
                        ->label(false) ?>
            </div>

            <div class="col-md-2 mb-3 mb-lg-0">
                <?= $form->field($model, 'year')
                        ->dropDownList(Set::getAvailableYearsList(), ['prompt' => T::tr('Any year')])
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
            const searchForm = document.getElementById('set-search-form');
            if (!searchForm) {
                return;
            }

            const selects = searchForm.querySelectorAll('select');
            selects.forEach((select) => {
                select.addEventListener('change', () => {
                    searchForm.submit();
                });
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