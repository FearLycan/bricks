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
        <i class="ph ph-user-circle"></i>
        <span><?= Html::encode(trim((string)($user->username ?? T::tr('User')))) ?></span>
    </button>

    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <?= Html::a(T::tr('Profile'), ['/user/profile'], [
                    'class'  => 'dropdown-item',
                    'encode' => false,
            ]) ?>
        </li>
        <li>
            <?= Html::a(T::tr('Wishlist'), ['/wishlist/index'], [
                    'class'  => 'dropdown-item',
                    'encode' => false,
            ]) ?>
        </li>
        <li>
            <?= Html::a(T::tr('Owned sets'), ['/owned-set/index'], [
                    'class'  => 'dropdown-item',
                    'encode' => false,
            ]) ?>
        </li>
        <li>
            <?= Html::a(T::tr('Settings'), ['/user/settings'], [
                    'class'  => 'dropdown-item',
                    'encode' => false,
            ]) ?>
        </li>

        <?php if ($user->isAdmin()): ?>
            <li>
                <?php $unread = \common\models\ContactMessage::unreadCount(); ?>
                <?= Html::a(Html::encode(T::tr('Admin panel')) . ($unread > 0 ? ' <span class="badge text-bg-danger" title="Unread messages">' . $unread . '</span>' : ''), Yii::$app->backendUrlManager->createAbsoluteUrl(['/']), [
                        'class'  => 'dropdown-item',
                        'encode' => false,
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
