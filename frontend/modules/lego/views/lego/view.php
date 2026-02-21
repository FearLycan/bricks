<?php

use common\components\Html;
use common\models\Set;
use common\widgets\InlineScript;
use frontend\components\Helper;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var $this  View
 * @var $model Set
 */

$this->title = Html::encode($model->name);
$this->params['breadcrumbs'][] = ['label' => Helper::getLegoName(), 'url' => ['/lego']];
$this->params['breadcrumbs'][] = ['label' => $model->theme->name, 'url' => ["/lego/theme/{$model->theme->slug}"]];

if ($model->subtheme) {
    $this->params['breadcrumbs'][] = ['label' => $model->subtheme->name, 'url' => ["/lego/theme/{$model->theme->slug}/{$model->subtheme->slug}"]];
}

$this->params['breadcrumbs'][] = $this->title;

?>

<div class="col">
    <div class="row product">
        <div class="col-md-6">


            <div id="carouselExampleDark" class="carousel carousel-dark slide">
                <div class="carousel-indicators">
                    <?php foreach ($model->images as $key => $image): ?>
                        <button type="button" data-bs-target="#carouselExampleDark" data-bs-slide-to="<?= $key ?>" class="<?= $image->isMain() ? 'active' : '' ?>" aria-current="true" aria-label="Slide <?= $image->id ?>"></button>
                    <?php endforeach; ?>
                </div>
                <div class="carousel-inner">
                    <?php foreach ($model->images as $image): ?>
                        <div class="carousel-item <?= $image->isMain() ? 'active' : '' ?>" data-bs-interval="10000">
                            <img src="<?= Url::to($image->url) ?>" class="d-block w-100" loading="lazy" alt="..." style="height: 500px; object-fit: cover;">
                            <!--<div class="carousel-caption d-none d-md-block">
                                <h5>First slide label</h5>
                                <p>Some representative placeholder content for the first slide.</p>
                            </div>-->
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleDark" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleDark" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>

            <!--
            <?= Html::img($model->getMainImage()->url ?? "https://placehold.co/650?text={$model->number}", [
                    'class'   => 'img-fluid',
                    'style'   => 'object-fit: fill; max-height:650px;',
                    'alt'     => Html::encode($model->name),
                    'id'      => 'mainImage',
                    'loading' => 'lazy',
            ]) ?>


            <div class="row mt-2 owl-main-content">

                <div class="col-md-12 owl-carousel owl-theme">
                    <?php foreach ($model->images as $image): ?>
                        <div class="item"
                             data-url="<?= Url::to($image->url) ?>"
                             style="height: 85px;
                                     background-image: url('<?= Url::to($image->url) ?>');
                                     background-position: center;
                                     background-repeat: no-repeat;
                                     background-size:cover;">
                        </div>
                    <?php endforeach; ?>

                </div>

                <div class="owl-theme">
                    <div class="owl-controls">
                        <div class="custom-nav owl-nav"></div>
                    </div>
                </div>

            </div>
            -->
        </div>

        <div class="col-md-6">
            <div class="summary entry-summary">
                <h1 class="product_title entry-title">
                    <?= $this->title ?>
                </h1>
                <p class="price">
                <span class="woocommerce-Price-amount amount">
                    <bdi>25,00&nbsp;
                        <span class="woocommerce-Price-currencySymbol">€</span>
                    </bdi>
                </span>
                </p>

                <div class="product_meta">

                    <div class="d-block">
                        <span>Theme:</span>
                        <span class="fw-bold"><?= Html::encode($model->theme->name) ?></span>
                    </div>

                    <div class="d-block">
                        <span>Theme group:</span>
                        <span class="fw-bold"><?= Html::encode($model->theme->group->name) ?></span>
                    </div>

                    <?php if ($model->theme->parent): ?>
                        <div class="d-block">
                            <span>Sub theme:</span>
                            <span class="fw-bold"><?= $model->theme->parent->name ?></span>
                        </div>
                    <?php endif; ?>


                    <div class="d-block">
                        <span>Year released:</span>
                        <span class="fw-bold"><?= Html::encode($model->year) ?></span>
                    </div>
                </div>

                <hr>

                <div class="container text-center">
                    <div class="row">
                        <div class="col">
                            <i class="bi bi-cake fs-2 d-block"></i>
                            <span class="fw-bold fs-2 d-block">
                                <?= Html::encode($model->age) ?>
                            </span>
                            <small class="text-body-secondary d-block">Age</small>
                        </div>

                        <div class="col">
                            <i class="bi bi-columns-gap fs-2 d-block"></i>
                            <span class="fw-bold fs-2 d-block"><?= Html::encode($model->pieces) ?></span>
                            <small class="text-body-secondary d-block">Pieces</small>
                        </div>

                        <div class="col">
                            <i class="bi bi-hash fs-2 d-block"></i>
                            <span class="fw-bold fs-2 d-block"><?= Html::encode($model->number) ?></span>
                            <small class="text-body-secondary d-block">Number</small>
                        </div>

                        <?php if ($model->minifigures): ?>
                            <div class="col">
                                <i class="bi bi-people fs-2 d-block"></i>
                                <span class="fw-bold fs-2 d-block"><?= Html::encode($model->minifigures) ?></span>
                                <small class="text-body-secondary d-block">Minifies</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>


                <div class="product_meta">
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <hr>
        </div>

        <div class="col-md-12 mt-2">
            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" href="#description" data-bs-target="#description" type="button" role="tab" aria-controls="description" aria-selected="true">
                        Description
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" href="#additional-information" data-bs-target="#additional-information" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">
                        Additional information
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="pills-contact-tab" data-bs-toggle="pill" href="#reviews" data-bs-target="#reviews" type="button" role="tab" aria-controls="pills-contact" aria-selected="false">
                        Reviews
                    </a>
                </li>
            </ul>
            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="pills-description-tab" tabindex="0">
                    description
                </div>
                <div class="tab-pane fade" id="additional-information" role="tabpanel" aria-labelledby="pills-additional-information-tab" tabindex="0">
                    additional information
                </div>
                <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="pills-reviews-tab" tabindex="0">
                    Reviews
                </div>
            </div>
        </div>

        <div class="col-md-12 mt-2">
            <section class="related products">
                <h2>Related products</h2>
                <div class="row g-4 mb-4 products">

                </div>
            </section>
        </div>
    </div>
</div>

<?php InlineScript::begin(); ?>
<script>
    //let owl = $('.owl-carousel');

    /*$(owl).owlCarousel({
        loop: true,
        margin: 10,
        dots: false,
        nav: true,
        navText: [
            '<i class="fa fa-angle-left" aria-hidden="true"></i>',
            '<i class="fa fa-angle-right" aria-hidden="true"></i>'
        ],
        navContainer: '.owl-main-content .custom-nav',
        items: 4,
        responsive: {
            0: {
                items: 3
            },
            600: {
                items: 3
            },
            1000: {
                items: 5
            }
        }
    });*/

    /*$(owl).on('click', 'div.item', function () {
        let url = $(this).data('url');

        let image = $(document).find('img#mainImage');
        image.fadeOut('swing', function () {
            image.attr('src', url);
            image.fadeIn('swing');
        });
    });*/

</script>
<?php InlineScript::end(); ?>
