<?php
use yii\db\Schema;
use yii\db\Migration;

class m150825_110035_create_table_parser_domains extends Migration
{
    private $_table = '{{parser_domains}}';

    public function init()
    {
        $this->db = 'dbSpider';
        parent::init();
    }

    public function safeUp()
    {
        $this->createTable($this->_table, [
            'id' => Schema::TYPE_PK,
            'domain' => 'varchar(255) NOT NULL',
            'begin_date' => 'DATETIME DEFAULT NULL',
            'finish_date' => 'DATETIME DEFAULT NULL',
            ], 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=MyISAM');
        
        $this->createIndex('domain', $this->_table, 'domain');
    }

    public function safeDown()
    {
        $this->dropTable($this->_table);
    }
}
