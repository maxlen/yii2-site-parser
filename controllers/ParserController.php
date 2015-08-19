<?php
namespace maxlen\parser\controllers;

use yii\console\Controller;
use yii\db\Exception;

use maxlen\parser\helpers\Parser;
use maxlen\parser\models\ParserLinks;
use maxlen\proxy\helpers\Proxy;

class ParserController extends Controller
{
   public function actionGrabLinks($domain, $linkId) {
       $params = Parser::getParams();
       $params['domain'] = $domain;

       Proxy::getRandomProxy();

       $link = ParserLinks::find()->where(['id' => $linkId])->limit(1)->one();

       Parser::grabLinks($link, $params);
   }
}
