<?php

use common\components\Html;
use common\models\Theme;
use frontend\components\Helper;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var $this View
 */

$this->title = Html::encode('LEGO');
$this->params['breadcrumbs'][] = ['label' => Helper::getLegoName()]


?>


<div class="col-lg-9">
    <h1 class="page-title">
        <?= Helper::getLegoName() ?>
    </h1>
    <div class="woocommerce-notices-wrapper"></div>
    <div class="row">
        <div class="col-md-6 col-lg-8 col-xxl-9"><p class="woocommerce-result-count"> Showing all 5 results</p></div>
        <div class="col-md-6 col-lg-4 col-xxl-3 mb-4">
            <form class="woocommerce-ordering" method="get"><select name="orderby" class="orderby custom-select" aria-label="Shop order">
                    <option value="menu_order" selected="selected">Default sorting</option>
                    <option value="popularity">Sort by popularity</option>
                    <option value="rating">Sort by average rating</option>
                    <option value="date">Sort by latest</option>
                    <option value="price">Sort by price: low to high</option>
                    <option value="price-desc">Sort by price: high to low</option>
                </select> <input type="hidden" name="paged" value="1">
            </form>
        </div>
    </div>
    <div class="row g-4 mb-4 products">
        <div class="col-md-6 col-lg-4 col-xxl-3">
            <div class="card h-100 d-flex text-center product type-product post-2606 status-publish first instock product_cat-plugins product_tag-dark-mode product_tag-plugin has-post-thumbnail downloadable virtual sold-individually taxable purchasable product-type-simple"><a href="https://bootscore.me/shop/plugins/bs-dark-mode/" class="woocommerce-LoopProduct-link woocommerce-loop-product__link"><img width="300" height="300" src="https://bootscore.me/wp-content/uploads/2020/10/dark-mode-300x300.webp" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="bS Dark Mode" decoding="async" fetchpriority="high"
                                                                                                                                                                                                                                                                                                                                                                                                                     srcset="https://bootscore.me/wp-content/uploads/2020/10/dark-mode-300x300.webp 300w, https://bootscore.me/wp-content/uploads/2020/10/dark-mode-150x150.webp 150w, https://bootscore.me/wp-content/uploads/2020/10/dark-mode-768x768.webp 768w, https://bootscore.me/wp-content/uploads/2020/10/dark-mode-600x600.webp 600w, https://bootscore.me/wp-content/uploads/2020/10/dark-mode-100x100.webp 100w, https://bootscore.me/wp-content/uploads/2020/10/dark-mode.webp 1000w"
                                                                                                                                                                                                                                                                                                                                                                                                                     sizes="(max-width: 300px) 100vw, 300px"></a>
                <div class="card-body d-flex flex-column"><a href="https://bootscore.me/shop/plugins/bs-dark-mode/" class="woocommerce-LoopProduct-link woocommerce-loop-product__link"><h2 class="woocommerce-loop-product__title">bs Dark Mode</h2> <span class="price"><span class="woocommerce-Price-amount amount"><bdi>10,00&nbsp;<span class="woocommerce-Price-currencySymbol">€</span></bdi></span> <small class="woocommerce-price-suffix">Inc. 19% VAT</small></span> </a>
                    <div class="add-to-cart-container mt-auto"><a href="?add-to-cart=2606" aria-describedby="woocommerce_loop_add_to_cart_link_describedby_2606" data-quantity="1" class="product_type_simple add_to_cart_button ajax_add_to_cart btn btn-primary w-100 mt-auto" data-product_id="2606" data-product_sku="BS0013" aria-label="Add to cart: “bs Dark Mode”" rel="nofollow" product-title="bs Dark Mode">
                            <div class="btn-loader"><span class="spinner-border spinner-border-sm"></span></div>
                            Add to cart</a></div>
                    <span id="woocommerce_loop_add_to_cart_link_describedby_2606" class="screen-reader-text"> </span></div>
            </div>
        </div>
    </div>
</div>

<div class="col-lg-3 order-first order-lg-2">
    <aside id="secondary" class="widget-area">
        <button class="d-lg-none btn btn-outline-primary w-100 mb-4 d-flex justify-content-between align-items-center" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar"> Open side menu <i class="fa-solid fa-ellipsis-vertical"></i></button>
        <div class="offcanvas-lg offcanvas-end" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel">
            <div class="offcanvas-header"><span class="h5 offcanvas-title" id="sidebarLabel">Sidebar</span>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body flex-column">
                <section id="block-19" class="widget mb-4">
                    <div class="wp-block-group card is-layout-flow wp-block-group-is-layout-flow"><h2 class="wp-block-heading card-header h6">Shop Categories</h2>
                        <div data-block-name="woocommerce/product-categories" data-has-image="true" data-is-hierarchical="false" class="wp-block-woocommerce-product-categories wc-block-product-categories list-group-flush is-list mb-0 " style="">
                            <ul class="wc-block-product-categories-list bs-list-group list-group wc-block-product-categories-list--depth-0 has-image">
                                <?php foreach (Theme::getMainThemes() as $theme): ?>
                                    <?php if ($theme->sets_count): ?>
                                        <li data-key="<?= $theme->id ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center wc-block-product-categories-list-item">
                                            <a class="stretched-link text-decoration-none" style="" href="https://bootscore.me/product-category/t-shirts/">
                                            <span class="wc-cat-img d-inline-block me-2">

                                                <?= Html::img(Url::to('@web/images/blank-category-image.png'), ['class' => 'attachment-woocommerce_thumbnail border rounded size-woocommerce_thumbnail',
                                                                                                                'alt'   => 'category',]) ?>

                                            </span>
                                                <span class="wc-block-product-categories-list-item__name align-middle">
                                                <?= $theme->name ?>
                                            </span>
                                            </a>
                                            <span class="badge bg-primary-subtle text-primary-emphasis">
                                            <span aria-hidden="true">
                                                <?= $theme->sets_count ?>
                                            </span>
                                            <span class="screen-reader-text"><?= $theme->sets_count ?> sets</span>
                                        </span>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </aside>
</div>

