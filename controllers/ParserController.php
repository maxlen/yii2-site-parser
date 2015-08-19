<?php namespace maxlen\parser\controllers;

use yii\console\Controller;
use yii\db\Exception;
use maxlen\parser\helpers\Parser;
use maxlen\parser\models\ParserLinks;
use maxlen\proxy\helpers\Proxy;

class ParserController extends Controller
{

    public function actionGrabLinks($domain, $linkId, $startNewProcess = 0)
    {
        $params = Parser::getParams();
        $params['domain'] = $domain;

        Proxy::getRandomProxy();

        $link = ParserLinks::find()->where(['id' => $linkId])->limit(1)->one();

        Parser::grabLinks($link, $params);

        if($startNewProcess != 0) {
            $link = ParserLinks::find()->where(['status' => Parser::TYPE_NOT_PARSED])->limit(1)->one();

            if (!is_null($link)) {
                $link->status = Parser::TYPE_PROCESS;
                $link->save();

                $command = "php yii parser/parser/grab-links {$params['domain']} {$link->id} 1 > /dev/null &";
                exec($command);
            }
        }
    }
}
