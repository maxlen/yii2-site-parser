<?php

use yii\db\Schema;
use yii\db\Migration;

use yii\helpers\ArrayHelper;
use yii\console\Controller;
use maxlen\proxy\helpers\Proxy;
use maxlen\proxy\models\ProxyAdwords;
use maxlen\proxy\models\ProxyBuy;
use maxlen\proxy\models\ProxySpider;
use maxlen\proxy\models\ProxyUkraine;
use maxlen\proxy\models\ProxyUsa;

use maxlen\proxy\models\Proxies;
use maxlen\proxy\models\ProxiesScopes;

class m150120_150553_create_tables_proxies_proxies_scopes extends Migration
{
    private $_table = '{{%proxies}}';
    private $_table2 = '{{%proxies_scopes}}';
    public function init() {
        $this->db = 'dbForms';
        parent::init();
    }
    
    public function safeUp() {
        $sql = "CREATE TABLE IF NOT EXISTS `proxy_log` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `ip` varchar(30) NOT NULL,
                  `search_engine` enum('google','yahoo','bing','yandex') DEFAULT 'google',
                  `code` int(3) unsigned NOT NULL,
                  `dt` datetime NOT NULL,
                  `dt_unblock` datetime NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `ip` (`ip`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
        $this->execute($sql);
        
        $sql = "CREATE TABLE IF NOT EXISTS `proxy_adwords` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `host` varchar(30) NOT NULL,
                  `port` varchar(10) NOT NULL,
                  `success` int(11) NOT NULL,
                  `failure` int(11) NOT NULL,
                  `active` tinyint(4) NOT NULL DEFAULT '1',
                  `login` varchar(50) NOT NULL,
                  `password` varchar(50) NOT NULL,
                  `yahoo_failure` int(11) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
        $this->execute($sql);
        
        $sql = "CREATE TABLE IF NOT EXISTS `proxy_buy` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `host` varchar(30) NOT NULL,
                  `port` varchar(10) NOT NULL,
                  `success` int(11) NOT NULL,
                  `failure` int(11) NOT NULL,
                  `active` tinyint(4) NOT NULL DEFAULT '1',
                  `login` varchar(50) NOT NULL,
                  `password` varchar(50) NOT NULL,
                  `yahoo_failure` int(11) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
        $this->execute($sql);
        
        $sql = "CREATE TABLE IF NOT EXISTS `proxy_spider` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `host` varchar(30) NOT NULL,
                  `port` varchar(10) NOT NULL,
                  `success` int(11) NOT NULL,
                  `failure` int(11) NOT NULL,
                  `active` tinyint(4) NOT NULL DEFAULT '1',
                  `login` varchar(50) NOT NULL,
                  `password` varchar(50) NOT NULL,
                  `yahoo_failure` int(11) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
        $this->execute($sql);
        
        
        $sql = "CREATE TABLE IF NOT EXISTS `proxy_ukraine` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `host` varchar(30) NOT NULL,
                  `port` varchar(10) NOT NULL,
                  `success` int(11) NOT NULL,
                  `failure` int(11) NOT NULL,
                  `active` tinyint(4) NOT NULL DEFAULT '1',
                  `login` varchar(50) NOT NULL,
                  `password` varchar(50) NOT NULL,
                  `yahoo_failure` int(11) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
        $this->execute($sql);
        
        
        $sql = "CREATE TABLE IF NOT EXISTS `proxy_usa` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `host` varchar(30) NOT NULL,
                  `port` varchar(10) NOT NULL,
                  `success` int(11) NOT NULL,
                  `failure` int(11) NOT NULL,
                  `active` tinyint(4) NOT NULL DEFAULT '1',
                  `login` varchar(50) NOT NULL,
                  `password` varchar(50) NOT NULL,
                  `yahoo_failure` int(11) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
        $this->execute($sql);
        
        $this->createTable($this->_table2, [
            'id' => Schema::TYPE_PK,
            'country' => 'varchar(3) DEFAULT NULL',
            'description' => 'varchar(255) DEFAULT NULL',
            'active' => 'tinyint(1) DEFAULT 1',
                ], 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=MyISAM');
        
        $this->createTable($this->_table, [
            'id' => Schema::TYPE_PK,
            'host' => 'varchar(30)',
            'port' => 'varchar(10)',
            'login' => 'varchar(50)',
            'password' => 'varchar(50)',
            'scope_id' => 'tinyint(4) DEFAULT NULL',
            'country' => 'varchar(3) DEFAULT NULL',
            'active' => 'tinyint(1) DEFAULT 1',
                ], 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=MyISAM');
        
        $this->restructuring();
    }
    
    public function safeDown() {
        $this->dropTable($this->_table2);
        $this->dropTable($this->_table);
        return true;
    }
    
    private function restructuring() {
        
        $countAllExist = $countToDelete = 0;
        $working = $this->getWorkingProxies();
//        var_dump($working);
//        die();
        
        $ourProxies = [];
        
        $tmpProxies = ProxyAdwords::find()->all();
        $oldProxies = [];
        $logins = [];
        if(!empty($tmpProxies)) {
            foreach($tmpProxies as $proxy) {
                $ourProxies[$proxy->host] = $proxy;
                if(!isset($logins[$proxy->login]))
                    $logins[$proxy->login] = ArrayHelper::toArray($proxy);
            }
        }
        
        $tmpProxies = ProxyBuy::find()->all();
        if(!empty($tmpProxies)) {
            foreach($tmpProxies as $proxy) {
                $ourProxies[$proxy->host] = $proxy;
                if(!isset($logins[$proxy->login]))
                    $logins[$proxy->login] = ArrayHelper::toArray($proxy);
            }
        }
        
        $tmpProxies = ProxySpider::find()->all();
        if(!empty($tmpProxies)) {
            foreach($tmpProxies as $proxy) {
                $ourProxies[$proxy->host] = $proxy;
                if(!isset($logins[$proxy->login]))
                    $logins[$proxy->login] = ArrayHelper::toArray($proxy);
            }
        }
        
        $tmpProxies = ProxyUkraine::find()->all();
        if(!empty($tmpProxies)) {
            foreach($tmpProxies as $proxy) {
                $ourProxies[$proxy->host] = $proxy;
                if(!isset($logins[$proxy->login]))
                    $logins[$proxy->login] = ArrayHelper::toArray($proxy);
            }
        }
        
        $tmpProxies = ProxyUsa::find()->all();
        if(!empty($tmpProxies)) {
            foreach($tmpProxies as $proxy) {
                $ourProxies[$proxy->host] = $proxy;
                if(!isset($logins[$proxy->login]))
                    $logins[$proxy->login] = ArrayHelper::toArray($proxy);
            }
        }
        
        $resProxies = [];
        $workingIPs = [];
        $scopeId = 1;
        $scopes = [];
        $counter = 1;
        if(!empty($ourProxies)) {
            foreach ($working as $country => $proxies) {
                $scopes[$scopeId] = ['id' => $scopeId, 'country' => $country];
                foreach($proxies as $proxy => $login) {

                    $workingIPs[] = $proxy;

                    $p = [];
                    $p['country'] = $country;
                    $p['scope_id'] = $scopeId;

                    if(array_key_exists($proxy, $ourProxies)) {
                        $tmpP = ArrayHelper::toArray($ourProxies[$proxy]);
                        $p['host'] = $tmpP['host'];
                        $p['login'] = $tmpP['login'];
                        $p['password'] = $tmpP['password'];
                        $p['port'] = $tmpP['port'];
                    }
                    else {
                        $p['host'] = $proxy;
                        $p['login'] = $login;
                        if(array_key_exists($login, $logins)) {
                            $p['password'] = $logins[$login]['password'];
                            $p['port'] = $logins[$login]['port'];
                        }
                        else {
                            $p['password'] = '';
                            $p['port'] = '0';
                            echo PHP_EOL.$login;
                        }
                    }
                    $resProxies[] = $p;

                    $countAllExist++;
                    $counter++;

                    if($counter>50) {
                        $scopeId++;
                        $scopes[$scopeId] = ['id' => $scopeId, 'country' => $country];
                        $counter = 0;
                    }
                }
                $scopeId++;
            }
        }
        
        foreach($ourProxies as $ip => $proxy) {
            if(!in_array($ip, $workingIPs)) {
                $countToDelete++;
            }
        }
        
        if(!empty($scopes)) {
            ProxiesScopes::getDb()->createCommand()
                    ->batchInsert(
                        ProxiesScopes::tableName(),
                        ['id', 'country'],
                        $scopes
                    )->execute();
        }
         
        if(!empty($resProxies)) {
            Proxies::getDb()->createCommand()
                    ->batchInsert(
                        Proxies::tableName(),
                        ['country', 'scope_id', 'host', 'login', 'password', 'port'],
                        $resProxies
                    )->execute();
        }
        
        echo "exists proxies: ".count($ourProxies)."; All email: ".$countAllExist."; Diff: ".(count($ourProxies) - $countAllExist)."; Delete: $countToDelete".PHP_EOL;
        
        $toFix = Proxies::find()->where(['password' => ''])->all();
        
        foreach ($toFix as $proxy) {
            echo PHP_EOL.$login;
            $pr = Proxies::find()->where(['login' => $proxy->login])->limit(1)->one();
            if(!is_null($pr)) {
                $proxy->port = $pr->port;
                $proxy->password = $pr->password;
                $proxy->save();
                echo ' fixed';
            }
        }
    }
    
    private function getWorkingProxies() {
        $workingIps = ['usa' => [], 'ru' => [], 'ua' => []];
        $workingIps['ru'] = [
            '134.0.119.124' => '', '134.0.119.140' => '', '134.0.119.163' => '', '134.0.119.197' => '', 
            '134.0.119.199' => '', '134.0.119.20' => '', '134.0.119.200' => '', '134.0.119.202' => '', 
            '134.0.119.203' => '', '134.0.119.205' => '', '213.183.58.163' => '', '213.183.58.19' => '', 
            '213.183.58.199' => '', '213.183.58.216' => '', '213.183.58.217' => '', '213.183.58.226' => '', 
            '213.183.58.227' => '', '213.183.59.134' => '', '213.183.59.107' => '', '213.183.59.21' => '', 
            '213.183.59.215' => '', '213.183.59.218' => '', '213.183.59.10' => '', '213.183.59.11' => '', 
            '213.183.59.136' => '', '213.183.60.118' => '', '213.183.60.108' => '', '213.183.60.110' => '', 
            '213.183.60.105' => '', '213.183.60.198' => '', '213.183.60.200' => '', '213.183.60.221' => '', 
            '213.183.60.127' => '', '213.183.60.135' => '', '213.183.60.136' => '', '213.183.60.137' => '', 
            '213.183.60.138' => '', '213.183.60.126' => '', '213.183.60.139' => '', '213.183.60.14' => '', 
            '213.183.61.215' => '', '213.183.61.211' => '', '213.183.61.212' => '', '213.183.61.213' => '', 
            '213.183.61.209' => '', '213.183.61.210' => '', '213.183.61.217' => '', '213.183.61.219' => '', 
            '213.183.61.220' => '', '213.183.61.221' => '', '213.183.62.173' => '', '213.183.62.174' => '', 
            '213.183.62.175' => '', '213.183.62.176' => '', '213.183.62.177' => ''];
        $workingIps['usa'] = ['173.214.164.53' => '', '173.214.164.55' => '', '173.214.164.60' => '', 
            '68.168.220.254' => '', '199.231.185.233' => '', '199.231.185.236' => '', '199.231.185.237' => '', 
            '199.231.185.229' => '', '199.231.185.231' => '','199.231.185.234' => '', '199.231.185.238' => ''];
        
        foreach($workingIps['ru'] as $ip => $val)
            $workingIps['ru'][$ip] = 'usa21409';
        foreach($workingIps['usa'] as $ip => $val)
            $workingIps['usa'][$ip] = 'usa21409';
        
        for($i=146; $i < 159; $i++)
            $workingIps['usa']['173.214.162.'.$i] = 'us311011';
        for($i=162; $i < 175; $i++)
            $workingIps['usa']['206.72.194.'.$i] = 'us311011';
        for($i=98; $i < 111; $i++)
            $workingIps['usa']['66.23.238.'.$i] = 'us311011';
        for($i=29; $i < 31; $i++)
            $workingIps['usa']['69.10.38.'.$i] = 'us311011';
        for($i=146; $i < 159; $i++)
            $workingIps['usa']['209.159.155.'.$i] = 'us311011';
        for($i=162; $i < 174; $i++)
            $workingIps['usa']['209.159.154.'.$i] = 'us311011';
        for($i=114; $i < 127; $i++)
            $workingIps['usa']['104.218.54.'.$i] = 'us311011';
        for($i=226; $i < 191; $i++)
            $workingIps['usa']['209.159.156.'.$i] = 'us311011';
        for($i=56; $i < 60; $i++)
            $workingIps['usa']['173.214.164.'.$i] = 'us311011';
        for($i=61; $i < 63; $i++)
            $workingIps['usa']['173.214.164.'.$i] = 'us311011';
        for($i=34; $i < 47; $i++)
            $workingIps['usa']['173.214.164.'.$i] = 'us311011';
        for($i=242; $i < 255; $i++)
            $workingIps['usa']['104.218.54.'.$i] = 'us311011';
        for($i=2; $i < 7; $i++)
            $workingIps['usa']['64.20.55.'.$i] = 'us311011';
        
        for($i=64; $i < 93; $i++)
            $workingIps['ru']['95.163.70.'.$i] = 'g1705';
        for($i=128; $i < 157; $i++)
            $workingIps['ru']['95.163.90.'.$i] = 'g1705';
        for($i=224; $i < 253; $i++)
            $workingIps['ru']['95.163.107.'.$i] = 'g1705';
        for($i=128; $i < 157; $i++)
            $workingIps['ru']['95.163.104.'.$i] = 'g1705';
        for($i=32; $i < 61; $i++)
            $workingIps['ru']['95.163.114.'.$i] = 'g1705';
        for($i=1; $i < 30; $i++)
            $workingIps['ru']['95.163.110.'.$i] = 'g1705';
        for($i=1; $i < 7; $i++)
            $workingIps['ru']['95.163.111.'.$i] = 'g1705';
        
        for($i=115; $i < 126; $i++)
            $workingIps['ua']['192.64.84.'.$i] = 'us40707';
        for($i=242; $i < 255; $i++)
            $workingIps['ua']['162.250.121.'.$i] = 'us40707';
        for($i=82; $i < 95; $i++)
            $workingIps['ua']['104.37.190.'.$i] = 'us40707';
        for($i=82; $i < 95; $i++)
            $workingIps['ua']['64.20.41.'.$i] = 'us40707';
        for($i=193; $i < 253; $i++)
            $workingIps['ua']['178.86.7.'.$i] = 'us40707';
        for($i=31; $i < 61; $i++)
            $workingIps['ua']['213.155.24.'.$i] = 'us40707';
        for($i=129; $i < 158; $i++)
            $workingIps['ua']['178.86.10.'.$i] = 'us40707';
        for($i=65; $i < 96; $i++)
            $workingIps['ua']['178.86.12.'.$i] = 'us40707';
            
        for($i=65; $i < 125; $i++)
            $workingIps['ua']['178.86.1.'.$i] = 'ua42511';
        for($i=128; $i < 189; $i++)
            $workingIps['ua']['178.86.14.'.$i] = 'ua42511';
        for($i=1; $i < 62; $i++)
            $workingIps['ua']['178.86.22.'.$i] = 'ua42511';
        for($i=1; $i < 19; $i++)
            $workingIps['ua']['178.86.24.'.$i] = 'ua42511';

        for($i=66; $i < 96; $i++)
            $workingIps['ru']['95.163.20.'.$i] = 'usa21409';
        for($i=2; $i < 17; $i++)
            $workingIps['ru']['95.163.30.'.$i] = 'usa21409';
        for($i=11; $i < 32; $i++)
            $workingIps['ru']['95.163.111.'.$i] = 'usa21409';
        for($i=1; $i < 32; $i++)
            $workingIps['ru']['95.163.115.'.$i] = 'usa21409';

        
        return $workingIps;
    }
}
