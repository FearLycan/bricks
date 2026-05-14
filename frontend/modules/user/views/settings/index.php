<?php

use common\models\User;
use common\models\UserSettings;
use frontend\components\T;
use frontend\modules\user\models\SettingsForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/**
 * @var View         $this
 * @var User         $user
 * @var SettingsForm $model
 */

$languageNames = [
    'en' => 'English',
    'pl' => 'Polski',
    'de' => 'Deutsch',
    'fr' => 'Français',
    'es' => 'Español',
    'it' => 'Italiano',
    'ja' => '日本語',
    'zh' => '中文',
];
$languageOptions = [];
foreach (UserSettings::SUPPORTED_LANGUAGES as $code) {
    $languageOptions[$code] = ($languageNames[$code] ?? $code) . ' (' . $code . ')';
}

$this->title = T::tr('Settings') . ' · ' . $user->username;
$this->params['metaDescription'] = T::tr('Manage your BrickAtlas preferences.');
$this->params['robots'] = 'noindex,nofollow';
$this->params['breadcrumbs'][] = ['label' => T::tr('Profile'), 'url' => ['/user/profile']];
$this->params['breadcrumbs'][] = T::tr('Settings');

?>

<div class="col-12 mt-4">
    <h1 class="page-title mb-1">
        <i class="bi bi-sliders text-primary me-2"></i>
        <?= Html::encode(T::tr('Settings')) ?>
    </h1>
    <p class="text-muted mb-4"><?= Html::encode(T::tr('Tweak how the catalogue behaves for your account.')) ?></p>

    <div class="row g-3">
        <div class="col-lg-8">
            <?php $form = ActiveForm::begin([
                'id'                     => 'user-settings-form',
                'enableClientValidation' => false,
                'fieldConfig'            => ['template' => "{input}"],
            ]); ?>

            <section class="user-page-card">
                <div class="d-flex justify-content-between align-items-baseline mb-2">
                    <h3 class="user-page-card-title">
                        <i class="bi bi-funnel-fill"></i><?= Html::encode(T::tr('Catalogue display')) ?>
                    </h3>
                    <span class="user-page-eyebrow"><?= Html::encode(T::tr('Preferences')) ?></span>
                </div>
                <div class="user-page-card-divider"></div>

                <div class="user-page-setting-row">
                    <div class="user-page-setting-text">
                        <p class="user-page-setting-label">
                            <?= Html::encode(T::tr('Hide sets I already own')) ?>
                        </p>
                        <p class="user-page-setting-hint">
                            <?= Html::encode(T::tr('When enabled, any set you marked as owned will be removed from the main catalogue, search and new-arrival listings.')) ?>
                        </p>
                    </div>
                    <div class="user-page-setting-switch form-check form-switch">
                        <?= Html::activeCheckbox($model, 'hide_owned_sets', [
                            'label'   => false,
                            'value'   => 1,
                            'uncheck' => '0',
                            'class'   => 'form-check-input',
                            'id'      => 'settings-hide-owned',
                            'role'    => 'switch',
                        ]) ?>
                    </div>
                </div>
            </section>

            <section class="user-page-card mt-3 d-none">
                <div class="d-flex justify-content-between align-items-baseline mb-2">
                    <h3 class="user-page-card-title">
                        <i class="bi bi-translate"></i><?= Html::encode(T::tr('Language')) ?>
                    </h3>
                    <span class="user-page-eyebrow"><?= Html::encode(T::tr('Preferences')) ?></span>
                </div>
                <div class="user-page-card-divider"></div>

                <div class="user-page-setting-row">
                    <div class="user-page-setting-text">
                        <p class="user-page-setting-label">
                            <?= Html::encode(T::tr('Preferred language')) ?>
                        </p>
                        <p class="user-page-setting-hint">
                            <?= Html::encode(T::tr('Choose the language used for the interface. Your preference is remembered between sessions and applied after signing in on any device.')) ?>
                        </p>
                    </div>
                    <div class="user-page-setting-switch">
                        <?= Html::activeDropDownList($model, 'preferred_language', $languageOptions, [
                            'class' => 'form-select',
                            'id'    => 'settings-preferred-language',
                        ]) ?>
                    </div>
                </div>
            </section>

            <div class="d-flex flex-wrap gap-2 mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check2 me-2"></i><?= Html::encode(T::tr('Save settings')) ?>
                </button>
                <?= Html::a(T::tr('Cancel'), ['/user/profile'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>

        <div class="col-lg-4">
            <section class="user-page-card">
                <h3 class="user-page-card-title mb-2">
                    <i class="bi bi-info-circle-fill"></i><?= Html::encode(T::tr('Tips')) ?>
                </h3>
                <p class="text-muted small mb-2">
                    <?= Html::encode(T::tr('Mark sets as owned with the box icon on each set card or on the set page.')) ?>
                </p>
                <p class="text-muted small mb-0">
                    <?= Html::encode(T::tr('Your preferences apply only to you and are saved instantly after pressing Save.')) ?>
                </p>
            </section>
        </div>
    </div>
</div>
