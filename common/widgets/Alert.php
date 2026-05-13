<?php

declare(strict_types=1);

namespace common\widgets;

use Yii;
use yii\bootstrap5\Alert as BootstrapAlert;
use yii\bootstrap5\Widget;

/**
 * Alert widget renders messages from session flash. All flash messages are displayed
 * in the sequence they were assigned using setFlash. You can set a message as follows:
 *
 * ```php
 * Yii::$app->session->setFlash('error', 'This is the message');
 * Yii::$app->session->setFlash('success', 'This is the message');
 * Yii::$app->session->setFlash('info', 'This is the message');
 * ```
 *
 * Multiple messages can be set per type:
 *
 * ```php
 * Yii::$app->session->setFlash('error', ['Error 1', 'Error 2']);
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
     * @var array<string, mixed>|false Options for the close button tag, or false to hide it.
     */
    public array|false $closeButton = [];

    public function run(): void
    {
        $session = Yii::$app->session;
        $flashes = $session->getAllFlashes();
        $appendClass = isset($this->options['class']) ? ' ' . $this->options['class'] : '';

        foreach ($flashes as $type => $flash) {
            if (!isset($this->alertTypes[$type])) {
                continue;
            }

            $iconClass = $this->alertIcons[$type] ?? '';

            foreach ((array)$flash as $i => $message) {
                echo BootstrapAlert::widget([
                    'body'        => $this->renderBody($iconClass, (string)$message),
                    'closeButton' => $this->closeButton,
                    'options'     => array_merge($this->options, [
                        'id'    => $this->getId() . '-' . $type . '-' . $i,
                        'class' => $this->alertTypes[$type] . ' d-flex align-items-center' . $appendClass,
                    ]),
                ]);
            }

            $session->removeFlash($type);
        }
    }

    private function renderBody(string $iconClass, string $message): string
    {
        $icon = $iconClass !== ''
            ? '<i class="bi ' . $iconClass . ' me-2 flex-shrink-0"></i>'
            : '';

        return $icon . '<span>' . $message . '</span>';
    }
}
