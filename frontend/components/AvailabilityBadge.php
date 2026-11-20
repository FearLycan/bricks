<?php

namespace frontend\components;

use yii\helpers\Html;

/**
 * Availability badge on set cards (listings, sliders).
 *
 * Brickset availability values are English and mostly "Retail"; plain
 * retail sets get no badge (it said nothing and covered the photo), the
 * rest get a short translated label. LEGO gifts with purchase get their own
 * purple "Gift" badge and a note where the price would be.
 */
final class AvailabilityBadge
{
    public const GWP = 'LEGO Gift with Purchase';

    public static function isGiftWithPurchase(?string $availability): bool
    {
        return $availability === self::GWP;
    }

    /** Badge HTML for the card photo, or '' when no badge is shown. */
    public static function render(?string $availability): string
    {
        $availability = trim((string)$availability);
        if ($availability === self::GWP) {
            return Html::tag(
                'span',
                Html::tag('i', '', ['class' => 'ph-fill ph-gift', 'aria-hidden' => 'true']) . Html::encode(T::tr('Free gift')),
                ['class' => 'set-card-availability-badge set-card-availability-badge--gwp']
            );
        }

        [$label, $modifier] = match ($availability) {
            'Retail - limited' => [T::tr('Limited availability'), 'available'],
            'LEGO exclusive'   => [T::tr('LEGO exclusive'), 'exclusive'],
            'Promotional'      => [T::tr('Promotional'), 'muted'],
            'Magazine gift'    => [T::tr('Magazine gift'), 'muted'],
            'Insiders Reward'  => [T::tr('LEGO Insiders reward'), 'muted'],
            'Educational'      => [T::tr('LEGO Education'), 'muted'],
            '', 'Retail', '{Not specified}' => [null, null],
            // Rare values we have no translation for: show them as they are.
            default            => [$availability, 'muted'],
        };
        if ($label === null) {
            return '';
        }

        return Html::tag('span', Html::encode($label), [
            'class' => 'set-card-availability-badge set-card-availability-badge--' . $modifier,
        ]);
    }

    /** Line shown instead of the price on gift-with-purchase cards. */
    public static function renderGwpNote(): string
    {
        return Html::tag(
            'div',
            Html::tag('i', '', ['class' => 'ph ph-gift', 'aria-hidden' => 'true']) . Html::encode(T::tr('Free with a LEGO order')),
            ['class' => 'set-card-gwp-note mt-2']
        );
    }
}
