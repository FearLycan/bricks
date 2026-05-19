<?php

use common\models\User;
use frontend\components\T;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/**
 * @var $user User
 */

?>

<div class="dropdown">
    <button class="bricks-user-btn dropdown-toggle d-flex align-items-center gap-2"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false">
        <i class="bi bi-person-circle"></i>
        <span><?= Html::encode(trim((string)($user->username ?? T::tr('User')))) ?></span>
    </button>

    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <?= Html::a('<i class="bi bi-person-vcard me-2"></i>' . T::tr('Profile'), ['/user/profile'], [
                    'class'  => 'dropdown-item',
                    'encode' => false,
            ]) ?>
        </li>
        <li>
            <?= Html::a('<i class="bi bi-heart me-2"></i>' . T::tr('Wishlist'), ['/wishlist/index'], [
                    'class'  => 'dropdown-item',
                    'encode' => false,
            ]) ?>
        </li>
        <li>
            <?= Html::a('<i class="bi bi-box-seam me-2"></i>' . T::tr('Owned sets'), ['/owned-set/index'], [
                    'class'  => 'dropdown-item',
                    'encode' => false,
            ]) ?>
        </li>
        <li>
            <?= Html::a('<i class="bi bi-sliders me-2"></i>' . T::tr('Settings'), ['/user/settings'], [
                    'class'  => 'dropdown-item',
                    'encode' => false,
            ]) ?>
        </li>

        <?php if ($user->isAdmin()): ?>
            <li>
                <?= Html::a('<i class="bi bi-person-square me-2"></i>' . T::tr('Admin panel'), Yii::$app->backendUrlManager->createAbsoluteUrl(['/']), [
                        'class' => 'dropdown-item',
                ]) ?>
            </li>
        <?php endif; ?>
        <li>
            <hr class="dropdown-divider">
        </li>
        <li>
            <?= Html::a(T::tr('Logout'), ['/auth/logout'], [
                    'class'       => 'dropdown-item',
                    'data-method' => 'post',
            ]) ?>
        </li>
    </ul>
</div>
