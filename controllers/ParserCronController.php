<?php

namespace maxlen\proxy\controllers;

use yii\console\Controller;
use yii\db\Exception;
use maxlen\proxy\models\ProxyBlocked;
use maxlen\proxy\models\Proxies;
use maxlen\proxy\helpers\Proxy;
use frontend\modules\auth\models\User;

class ParserController extends Controller
{
    private $_badProxies = [];
    private $_countBadProxies = 0;

    public function actionBadProxy()
    {   
        $this->checkProxies(Proxies::find()->all());

        if($this->_countBadProxies != 0) {
            $reportSettings = $this->module->reportSettings;
            $to = [];
            foreach($reportSettings['to'] as $alias) {
                $user = User::findByAlias($alias);
                if (!is_null($user)) {
                    $to[] = $user->email;
                }
            }
        
            \Yii::$app->mailer
                ->compose('/proxyBlockReport', ['count_total' => $this->_countBadProxies, 'data' => $this->_badProxies])
                ->setFrom($reportSettings['from'])
                ->setTo($to)
                ->setSubject('Proxy Not Worked')
                ->send();
        }
    }
    
    /**
     * 
     */
    public function actionProxyLogReport()
    {   
//        $DB_S->query("DELETE fqd, fd, fs, fq 
//FROM spider_forms_queries_doc AS fqd 
//LEFT JOIN spider_forms_doc AS fd ON fd.query_doc_id = fqd.id 
//LEFT JOIN spider_forms_sites AS fs ON fqd.site_id = fs.id 
//LEFT JOIN spider_forms_queries AS fq ON fs.query_id = fq.id 
//WHERE fq.part = '3B' AND fd.id IS NULL 
//AND fs.status_doc !=0");
        
        $results = \maxlen\proxy\models\ProxyLog::getDb()->createCommand("
                SELECT pl.*, p.scope_id FROM proxy_log AS pl
                LEFT JOIN ".Proxies::tableName(true)." AS p ON p.host = pl.ip 
                WHERE pl.dt > '".date("Y-m-d H:i:s",time()-86400)."' ORDER BY pl.ip
                ")->queryAll();

        $data = [];
        foreach($results as $res) {
            if(!isset($data[$res['ip']])) {
                $data[$res['ip']] = [
                    'ip' => $res['ip'],
                    'scope' => $res['scope_id'],
                    'total' => 0,
                    'google' => 0,
                    'yandex' => 0,
                    'yahoo' => 0,
                    'bing' => 0];
            }
            $data[$res['ip']][$res['search_engine']]++;
            $data[$res['ip']]['total']++; 
        }
        
        if(!empty($data)) {
            \Yii::$app->mailer
                ->compose("/proxyLogReport", ['count_total' => count($data), 'data' => $data])
                ->setFrom('maxim.gavrilenko@pdffiller.com')
                ->setTo(['maxim.gavrilenko@pdffiller.com', 'koshevchenko@gmail.com', 'andrew.t@pdffiller.com'])
                ->setSubject('Spider Log')
                ->send();
        }
    }
    
    /**
     * Fill blocked proxies
     * @param object $proxyModel
     * @param string $table
     */
    private function checkProxies($proxyModel) {
        
        foreach($proxyModel as $proxyM) {
            $proxy = [
                'login' => $proxyM->login,
                'password' => $proxyM->password,
                'host' => $proxyM->host,
                'port' => $proxyM->port,
                'scope_id' => $proxyM->scope_id,
                ];
            
            $res = Proxy::getHTML('http://pdffiller.com.ua', ['proxy' => $proxy, 'getInfo' => true]);
            
            if(isset($res['info'])) {
                if($res['info']['http_code'] == 0) {
                    $res = Proxy::getHTML('http://pdffiller.com', ['proxy' => $proxy, 'getInfo' => true]);
                    if(isset($res['info'])) {
                        if($res['info']['http_code'] == 0) {
                            $this->_badProxies[] = $proxy;
                            $this->_countBadProxies++;

                            $blockedProxy = new ProxyBlocked();
                            $blockedProxy->ip = $proxyM->host;
//                            $blockedProxy->table = $table;
                            $blockedProxy->create_date = date('Y-m-d H:i:s');
                            $blockedProxy->save();
                        }
                    }
                }
            }
        } 
    }
}
