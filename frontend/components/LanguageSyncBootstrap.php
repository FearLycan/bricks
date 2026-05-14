<?php

namespace frontend\components;

use common\models\User;
use yii\base\ActionEvent;
use yii\base\BootstrapInterface;
use yii\web\Application;

/**
 * Synchronises the active language with the logged-in user's saved preference.
 *
 * - When the active URL language differs from the persisted preference (e.g.
 *   the user clicked the flag in the language switcher), the new choice is
 *   written back so logging in on another device picks it up.
 * - The post-login redirect itself is performed inside `AuthController` so we
 *   can rewrite the returnUrl with the correct language prefix.
 */
final class LanguageSyncBootstrap implements BootstrapInterface
{
    public function bootstrap($app): void
    {
        if (!$app instanceof Application) {
            return;
        }

        $app->on(Application::EVENT_BEFORE_ACTION, function (ActionEvent $event) use ($app): void {
            if ($app->user->isGuest) {
                return;
            }

            $identity = $app->user->identity;
            if (!$identity instanceof User) {
                return;
            }

            $current = (string)$app->language;
            if (!in_array($current, SeoHelper::SUPPORTED_LANGUAGES, true)) {
                return;
            }

            $settings = $identity->getOrCreateSettings();
            if ($settings->preferred_language === $current) {
                return;
            }

            $settings->preferred_language = $current;
            $settings->save(false);
        });
    }
}
