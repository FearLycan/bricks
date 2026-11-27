<?php

namespace frontend\controllers;

use common\components\TrafficClassifier;
use common\models\OfferClick;
use common\models\Set;
use common\models\SetOffer;
use common\models\User;
use frontend\components\BotGuard;
use Throwable;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * POST /click  (kind=offer&id=<set_offer id> | kind=lego&id=<set id>, lang, CSRF)
 *
 * Sent by js/offer-tracking.js with navigator.sendBeacon when a visitor opens a store
 * offer or the official LEGO price, so every row comes from a browser that ran our JS.
 * Set, store and price are read from the database, never from the request. Admins are
 * skipped; crawlers, foreign referrers and very busy connections are kept as is_bot = 1.
 * Always answers 204 so the page never waits on it.
 */
final class ClickController extends Controller
{
    /** Clicks per connection and hour before the rest count as bot clicks. */
    private const HUMAN_PER_HOUR = 40;
    /** The same click repeated within this many seconds is a double click, not a new one. */
    private const DEDUPE_SECONDS = 20;

    public function behaviors(): array
    {
        return ['verbs' => ['class' => VerbFilter::class, 'actions' => ['record' => ['POST']]]];
    }

    public function actionRecord(): Response
    {
        $response = Yii::$app->response;
        $response->statusCode = 204;
        $response->headers->set('Cache-Control', 'no-store');

        try {
            $this->record();
        } catch (Throwable $e) {
            Yii::warning('Offer click not recorded: ' . $e->getMessage(), __METHOD__);
        }

        return $response;
    }

    private function record(): void
    {
        $identity = Yii::$app->user->getIdentity(false);
        if ($identity instanceof User && $identity->isAdmin()) {
            return;
        }
        $request = Yii::$app->request;
        $kind = (string)$request->post('kind', '');
        $id = (int)$request->post('id', 0);
        if ($id <= 0) {
            return;
        }

        $click = new OfferClick();
        if ($kind === OfferClick::KIND_OFFER) {
            $offer = SetOffer::findOne($id);
            if ($offer === null || !$offer->url) {
                return;
            }
            $click->set_id = (int)$offer->set_id;
            $click->set_offer_id = (int)$offer->id;
            $click->store_id = (int)$offer->store_id;
            $click->price = $offer->price !== null ? (int)$offer->price : null;
            $click->currency_code = $offer->currency_code ? strtoupper((string)$offer->currency_code) : null;
        } elseif ($kind === OfferClick::KIND_LEGO) {
            if (!Set::find()->where(['id' => $id])->exists()) {
                return;
            }
            $click->set_id = $id;
        } else {
            return;
        }
        $click->kind = $kind;

        $ua = (string)$request->userAgent;
        $connection = hash('sha256', $request->userIP . '|' . $ua . '|' . $request->cookieValidationKey);
        $cache = Yii::$app->cache;
        if (!$cache->add(['offer-click', $connection, $kind, $id], 1, self::DEDUPE_SECONDS)) {
            return;
        }
        $hourKey = ['offer-click-hour', $connection, (int)(time() / 3600)];
        $count = (int)$cache->get($hourKey) + 1;
        $cache->set($hourKey, $count, 3600);

        $lang = (string)$request->post('lang', '');
        $click->lang = preg_match('/^[a-z]{2}(-[A-Za-z]{2})?$/', $lang) ? $lang : null;
        $click->device = TrafficClassifier::device($ua);
        $click->ua_hash = $ua === '' ? null : hash('sha256', $ua);
        $click->is_bot = self::looksAutomated($ua, (string)$request->referrer, (string)parse_url($request->hostInfo, PHP_URL_HOST))
            || $count > self::HUMAN_PER_HOUR;
        $click->created_at = time();
        $click->save(false);
    }

    /** A real click comes from one of our pages, so the referrer is our own host. */
    public static function looksAutomated(string $ua, string $referrer, string $ownHost): bool
    {
        if ($ua === '' || TrafficClassifier::bot($ua) !== null || BotGuard::isAutomated()) {
            return true;
        }
        $refHost = (string)parse_url($referrer, PHP_URL_HOST);

        return $refHost === '' || strcasecmp($refHost, $ownHost) !== 0;
    }
}
