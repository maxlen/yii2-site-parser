<?php namespace maxlen\parser\controllers;

use yii\console\Controller;
use yii\db\Exception;
use maxlen\parser\helpers\Parser;
use maxlen\parser\models\ParserDomains;
use maxlen\parser\models\ParserLinks;
use maxlen\proxy\helpers\Proxy;

class ParserController extends Controller
{

    public function actionGrabLinks($domainId, $linkId, $startNewProcess = 0)
    {
        $params = Parser::getParams();
        
        $domain = ParserDomains::find()->where(['id' => $domainId])->limit(1)->one();
        
        if(is_null($domain)) {
            echo PHP_EOL. " THERE IS NO DOMAIN id = {$domainId} IN DB". PHP_EOL;
            return;
        }
            
        $params['domain'] = $domain;

        Proxy::getRandomProxy();

        $link = ParserLinks::find()->where(['id' => $linkId])->limit(1)->one();

        if(is_null($link)) {
            return;
        }
        
        Parser::grabLinks($link, $params);

        if($startNewProcess != 0) {
            $link = ParserLinks::find()->where(['status' => Parser::TYPE_NOT_PARSED])->limit(1)->one();

            if (!is_null($link)) {
                $link->status = Parser::TYPE_PROCESS;
                $link->save();

                $command = "php yii parser/parser/grab-links {$domain->id} {$link->id} 1 > /dev/null &";
                exec($command);
            }
        }
    }
}
