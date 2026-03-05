<?php

use common\components\Html;
use common\models\Theme;
use common\widgets\InlineScript;

/**
 * @var Theme $model
 */


?>

<?php if ($model && $model->img): ?>
    <div id="presentationImg" class="position-relative overflow-hidden text-center bg-body-tertiary d-none" style="background-image: url('<?= $model->img ?>');">
        <div class="col-md-6 p-lg-5 mx-auto my-5">

            <?php if ($model->name): ?>
                <span class="display-3 fw-bold d-block" id="presentationTitle">
                    <?= Html::encode($model->name) ?>
                </span>
            <?php endif; ?>

            <?php if ($model->description): ?>
                <span class="fw-normal text-muted d-block" id="presentationSubtitle">
                    <?= Html::encode($model->description) ?>
                </span>
            <?php endif; ?>

        </div>
    </div>

    <style>
        main > .container, main > .container-fluid {
            padding: 0 15px 20px;
        }

        <?php if ($model->custom_css): ?>
        <?= $model->custom_css ?>
        <?php endif; ?>
    </style>

    <?php InlineScript::begin(); ?>
    <script>
        (() => {
            const presentationImage = document.getElementById('presentationImg');
            const presentationTarget = document.getElementById('presentation');

            presentationTarget.append(presentationImage);
            presentationImage.classList.remove('d-none');
        })();
    </script>
    <?php InlineScript::end(); ?>
<?php endif; ?>
