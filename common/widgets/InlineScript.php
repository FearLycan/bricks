<?php

namespace common\widgets;

use Exception;
use yii\base\Widget;
use yii\web\View;

class InlineScript extends Widget
{

    public string  $position;
    public ?string $key;

    public function init(): void
    {
        parent::init();
        ob_start();
    }

    public function run(): void
    {
        $this->position = empty($this->position) ? View::POS_READY : $this->position;
        $this->key = empty($this->key) ? uniqid('keyJS', false) : $this->key;

        $content = ob_get_clean();

        $scripts = null;
        if (preg_match('/<script.*?>(.*)<\/script>/s', $content, $scripts)) {
            $this->view->registerJs(trim($scripts[1]), $this->position, $this->key);
        } else {
            throw new Exception("No script was found. You need to include your script between the <script> tags.");
        }
    }

}