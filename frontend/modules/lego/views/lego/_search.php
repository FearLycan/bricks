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
            <div class="col-md-4">
                <?= $form->field($model, 'name')
                        ->label(false) ?>
            </div>

            <div class="col-md-4">
                <?= $form->field($model, 'theme_id')
                        ->dropDownList(Set::getAvailableThemesList(), ['prompt' => T::tr('Any theme')])
                        ->label(false) ?>
            </div>

            <div class="col-md-3">
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
            const selects = searchForm.querySelectorAll('select');
            selects.forEach((select) => {
                select.addEventListener('change', () => {
                    searchForm.submit();
                });
            });
        })();
    </script>
<?php InlineScript::end(); ?>