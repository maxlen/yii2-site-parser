<?php

namespace maxlen\parser;

use yii\console\Exception;

class Parser extends \yii\base\Module
{
    public $controllerNamespace = 'maxlen\parser\controllers';

    public $reportSettings = [];

    public function init()
    {
        parent::init();
        if(empty($this->reportSettings)) {
            throw new Exception('Empty reportSettings');
        }
    }
}
