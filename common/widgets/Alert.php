<?php

declare(strict_types=1);

namespace common\widgets;

use Yii;
use yii\bootstrap5\Alert as BootstrapAlert;
use yii\bootstrap5\Widget;
use yii\helpers\Html;

/**
 * Alert widget renders messages from session flash.
 *
 * - `success` and `info` flashes are rendered as Bootstrap Toasts in a fixed
 *   container (top-right on desktop, full-width bottom on mobile).
 * - `error`, `danger` and `warning` flashes are rendered as inline alerts so
 *   they stay on the page until the user reads them.
 *
 * ```php
 * Yii::$app->session->setFlash('success', 'Saved.');
 * Yii::$app->session->setFlash('error', 'Something went wrong.');
 * ```
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @author Alexander Makarov <sam@rmcreative.ru>
 */
class Alert extends Widget
{
    /**
     * @var array<string, string> Map of a flash key to Bootstrap alert class.
     */
    public array $alertTypes = [
        'error'   => 'alert-danger',
        'danger'  => 'alert-danger',
        'success' => 'alert-success',
        'info'    => 'alert-info',
        'warning' => 'alert-warning',
    ];

    /**
     * @var array<string, string> Map of a flash key to Bootstrap Icons class.
     */
    public array $alertIcons = [
        'error'   => 'bi-exclamation-octagon-fill',
        'danger'  => 'bi-exclamation-octagon-fill',
        'success' => 'bi-check-circle-fill',
        'info'    => 'bi-info-circle-fill',
        'warning' => 'bi-exclamation-triangle-fill',
    ];

    /**
     * @var array<int, string> Flash keys rendered as toasts instead of inline alerts.
     */
    public array $toastTypes = ['success', 'info'];

    /**
     * @var array<string, string> Map of toast flash key to Bootstrap background class.
     */
    public array $toastBackgrounds = [
        'success' => 'text-bg-success',
        'info'    => 'text-bg-info',
    ];

    /**
     * @var int Toast auto-hide delay in milliseconds.
     */
    public int $toastDelay = 5000;

    /**
     * @var array<string, mixed>|false Options for the close button on inline alerts.
     */
    public array|false $closeButton = [];

    public function run(): void
    {
        $session = Yii::$app->session;
        $flashes = $session->getAllFlashes();
        $appendClass = isset($this->options['class']) ? ' ' . $this->options['class'] : '';

        $toastQueue = [];

        foreach ($flashes as $type => $flash) {
            if (!isset($this->alertTypes[$type])) {
                continue;
            }

            $messages = (array)$flash;

            if (in_array($type, $this->toastTypes, true)) {
                foreach ($messages as $message) {
                    $toastQueue[] = [(string)$type, (string)$message];
                }
                $session->removeFlash($type);
                continue;
            }

            $iconClass = $this->alertIcons[$type] ?? '';

            foreach ($messages as $i => $message) {
                echo BootstrapAlert::widget([
                    'body'        => $this->renderInlineBody($iconClass, (string)$message),
                    'closeButton' => $this->closeButton,
                    'options'     => array_merge($this->options, [
                        'id'    => $this->getId() . '-' . $type . '-' . $i,
                        'class' => $this->alertTypes[$type] . ' d-flex align-items-center bx-flash-alert' . $appendClass,
                    ]),
                ]);
            }

            $session->removeFlash($type);
        }

        if ($toastQueue !== []) {
            echo $this->renderToastContainer($toastQueue);
        }
    }

    private function renderInlineBody(string $iconClass, string $message): string
    {
        $icon = $iconClass !== ''
            ? '<i class="bi ' . $iconClass . ' me-2 flex-shrink-0"></i>'
            : '';

        return $icon . '<span>' . $message . '</span>';
    }

    /**
     * @param array<int, array{0:string,1:string}> $queue
     */
    private function renderToastContainer(array $queue): string
    {
        $items = '';
        foreach ($queue as [$type, $message]) {
            $items .= $this->renderToast($type, $message);
        }

        return '<div class="toast-container bx-toast-container position-fixed top-0 end-0 p-3" aria-live="polite" aria-atomic="true">'
            . $items
            . '</div>';
    }

    private function renderToast(string $type, string $message): string
    {
        $bg = $this->toastBackgrounds[$type] ?? 'text-bg-secondary';
        $iconClass = $this->alertIcons[$type] ?? '';
        $icon = $iconClass !== ''
            ? '<i class="bi ' . $iconClass . ' me-2 flex-shrink-0 fs-5"></i>'
            : '';

        $body = '<div class="toast-body d-flex align-items-center">'
            . $icon
            . '<span>' . Html::encode($message) . '</span>'
            . '</div>';

        $close = '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>';

        return '<div class="toast bx-flash-toast align-items-center border-0 ' . $bg . '"'
            . ' role="alert" aria-live="assertive" aria-atomic="true"'
            . ' data-bs-autohide="true" data-bs-delay="' . (int)$this->toastDelay . '">'
            . '<div class="d-flex">' . $body . $close . '</div>'
            . '</div>';
    }
}
