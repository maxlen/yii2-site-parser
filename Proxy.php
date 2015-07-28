<?php

namespace maxlen\proxy;

class Proxy extends \yii\base\Module
{
    public $controllerNamespace = 'maxlen\proxy\controllers';

    public $reportSettings = [];

    public function init()
    {
        parent::init();
        if(empty($this->reportSettings)) {
            throw new Exception('Empty reportSettings');
        }
    }
}
