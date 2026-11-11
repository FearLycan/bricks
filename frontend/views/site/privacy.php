<?php

/** @var yii\web\View $this */

use frontend\components\SeoHelper;
use frontend\components\T;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = T::tr('Privacy Policy');
$this->params['metaDescription'] = T::tr('How BrickAtlas handles personal data, cookies, analytics, ads and affiliate links.');
$this->params['canonicalUrl'] = SeoHelper::buildAbsoluteUrl(['/site/privacy']);
$this->params['breadcrumbs'][] = T::tr('Privacy Policy');

// Legal identity of the data controller. Set 'privacy.operator' in
// params-local.php (e.g. "Jan Kowalski, Firma XYZ, NIP 0000000000").
$operator = trim((string)(Yii::$app->params['privacy.operator'] ?? '')) ?: 'BrickAtlas.shop';
$contact = Html::a(Html::encode(T::tr('contact form')), Url::to(['/site/contact']));

$sections = [
    T::tr('Who we are') => [
        T::tr('BrickAtlas.shop ("BrickAtlas", "we") is an independent LEGO® set catalogue and price comparison site. The data controller is {operator}. You can reach us through the {contact}.', [
            'operator' => Html::encode($operator),
            'contact'  => $contact,
        ]),
    ],
    T::tr('What data we collect') => [
        T::tr('Contact form: your name, email address and message. We use them only to reply to you and keep them no longer than needed to handle your request.'),
        T::tr('User account (optional): email address, username and the settings, reviews and lists you save. You can ask us to delete your account at any time.'),
        T::tr('Analytics: we use Google Analytics 4 to understand how the site is used (pages viewed, approximate location, device type). Google may set cookies for this purpose.'),
        T::tr('Advertising: some pages show ads served by Google AdSense. Google and its partners may use cookies to show and measure ads, including personalised ads where permitted. You can manage ad personalisation at adssettings.google.com.'),
        T::tr('Server logs: our hosting provider records technical data such as IP address, browser and requested URL to keep the service secure and working. Logs are kept for a limited time.'),
    ],
    T::tr('Cookies') => [
        T::tr('We use essential cookies (session, security token, language choice) as well as analytics and advertising cookies from Google. You can block or delete cookies in your browser settings; the site works without analytics and advertising cookies.'),
    ],
    T::tr('Affiliate links') => [
        T::tr('Some links to stores are affiliate links. When you click one and make a purchase, the store or affiliate network (for example Rakuten Advertising or Tradedoubler) may set its own cookie to attribute the sale, and we may earn a commission. This does not change the price you pay. The store\'s own privacy policy applies once you leave BrickAtlas.'),
    ],
    T::tr('Legal basis and sharing') => [
        T::tr('We process data to answer your messages and provide your account (performance of a service), and to run, secure and improve the site (our legitimate interest). We do not sell personal data. Data is shared only with providers we rely on: hosting (MyDevil, Poland), Google (analytics and ads) and, if you click an affiliate link, the relevant store or network.'),
    ],
    T::tr('Your rights') => [
        T::tr('Under the GDPR you have the right to access, correct, delete or restrict processing of your data, to object to processing, and to data portability. To exercise these rights, contact us through the {contact}. You may also lodge a complaint with the Polish data protection authority (Prezes Urzędu Ochrony Danych Osobowych, uodo.gov.pl) or your local supervisory authority.', [
            'contact' => $contact,
        ]),
    ],
    T::tr('Changes') => [
        T::tr('We may update this policy as the site evolves. The date at the top shows the latest version.'),
    ],
];
?>
<div class="site-privacy col-lg-9">
    <h1><?= Html::encode($this->title) ?></h1>
    <p class="text-body-secondary small">
        <?= Html::encode(T::tr('Last updated: {date}', ['date' => Yii::$app->formatter->asDate('2026-11-11', 'long')])) ?>
    </p>

    <?php foreach ($sections as $heading => $paragraphs): ?>
        <h2 class="h5 mt-4"><?= Html::encode($heading) ?></h2>
        <?php if (count($paragraphs) > 1): ?>
            <ul>
                <?php foreach ($paragraphs as $paragraph): ?>
                    <li class="mb-2"><?= $paragraph ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p><?= $paragraphs[0] ?></p>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
