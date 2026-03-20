<?php

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */

/** @var Exception $exception */

use frontend\components\T;
use yii\helpers\Html;

$this->title = $name;
$statusCode = (int)($exception->statusCode ?? 500);
$errorHeadline = T::tr('Error {code}', [
    'code' => (string)$statusCode,
]);
$subtitle = match ($statusCode) {
    403 => T::tr('You do not have permission to open this page.'),
    404 => T::tr('The page you requested could not be found.'),
    500 => T::tr('Internal server error. Our builders are fixing it right now.'),
    default => T::tr('Something went wrong while processing your request.'),
};
$brickJoke = match ($statusCode) {
    403 => T::tr('This section is protected by a very serious LEGO guard minifigure.'),
    404 => T::tr('Looks like this page is a missing piece from the set.'),
    500 => T::tr('Oops. Someone stepped on a 2x4 brick in the server room.'),
    default => T::tr('A loose brick rolled into the request pipeline.'),
};

$this->registerCss('
    .site-error-panel {
        display: grid;
        grid-template-columns: 220px 1fr;
        gap: 1.2rem;
        border: 1px solid #e5e9f0;
        border-radius: 0.9rem;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(19, 31, 55, 0.08);
    }
    .site-error-side {
        background: #f7f9fc;
        border-right: 1px solid #e5e9f0;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.8rem;
    }
    .site-error-logo {
        width: 140px;
        height: 140px;
        object-fit: contain;
        border-radius: 0.7rem;
        border: 1px solid #d8deea;
        background: #fff;
        padding: 0.3rem;
    }
    .site-error-side-code {
        font-size: 0.9rem;
        color: #5d6b82;
        font-weight: 600;
    }
    .site-error-bricks {
        position: relative;
        width: 72px;
        height: 46px;
        border: 2px solid #d1d8e4;
        border-radius: 0.5rem;
        background: #eef3fb;
    }
    .site-error-bricks::before,
    .site-error-bricks::after {
        content: "";
        position: absolute;
        top: -12px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 2px solid #d1d8e4;
        background: #f6f9ff;
    }
    .site-error-bricks::before {
        left: 14px;
    }
    .site-error-bricks::after {
        left: 38px;
    }
    .site-error-main {
        padding: 1rem 1.2rem 1.2rem;
    }
    .site-error-heading {
        margin: 0 0 0.45rem;
        font-size: clamp(1.8rem, 3.4vw, 2.4rem);
        line-height: 1.2;
    }
    .site-error-subtitle {
        margin: 0 0 1rem;
        color: #5d6778;
    }
    .site-error-joke {
        margin: -0.55rem 0 1rem;
        color: #3f4b5e;
        font-weight: 500;
    }
    .site-error-categories {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 1rem;
    }
    .site-error-cta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin: 0 0 1rem;
    }
    .site-error-popular {
        margin-top: 1rem;
        padding: 0.75rem 0.85rem;
        border: 1px solid #e6ebf3;
        border-radius: 0.75rem;
        background: #f8fafd;
    }
    .site-error-popular-title {
        margin: 0 0 0.55rem;
        font-weight: 600;
        color: #4a5568;
        font-size: 0.92rem;
    }
    .site-error-popular-links {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
    }
    .site-error-500-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.75rem;
    }
    @media (max-width: 767px) {
        .site-error-panel {
            grid-template-columns: 1fr;
        }
        .site-error-side {
            border-right: 0;
            border-bottom: 1px solid #e5e9f0;
        }
    }
');
?>
<div class="site-error">

    <section class="site-error-panel">
        <aside class="site-error-side">
            <?= Html::img('@web/images/logo-social.png', [
                    'alt'   => Yii::$app->name,
                    'class' => 'site-error-logo',
            ]) ?>
            <?= Html::a(T::tr('Go to homepage'), ['/'], ['class' => 'btn btn-sm btn-outline-secondary site-error-side-code']) ?>
        </aside>

        <div class="site-error-main">
            <h1 class="site-error-heading"><?= Html::encode($this->title) ?></h1>
            <p class="site-error-subtitle"><?= Html::encode($subtitle) ?></p>
            <p class="site-error-joke"><?= Html::encode($brickJoke) ?></p>

            <div class="site-error-cta">
                <?= Html::a(T::tr('Back to catalog'), ['/lego'], ['class' => 'btn btn-sm btn-primary']) ?>
                <?= Html::a(T::tr('See popular sets'), ['/'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                <?= Html::a(T::tr('Browse all themes'), ['/lego/theme/icons'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
            </div>

            <div class="alert alert-danger mb-3">
                <?= nl2br(Html::encode($message)) ?>
            </div>

            <p>
                <?= T::tr('The above error occurred while the Web server was processing your request.') ?>
            </p>
            <p class="mb-0">
                <?= T::tr('Please contact us if you think this is a server error. Thank you.') ?>
            </p>
            
            <div class="site-error-categories">
                <?= Html::a(T::tr('Star Wars'), ['/lego/theme/star-wars'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                <?= Html::a(T::tr('Icons'), ['/lego/theme/icons'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                <?= Html::a(T::tr('Technic'), ['/lego/theme/technic'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                <?= Html::a(T::tr('Botanicals'), ['/lego/theme/botanical-collection'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
            </div>

        </div>
    </section>

</div>
