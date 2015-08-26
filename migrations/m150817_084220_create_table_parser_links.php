<?php
use yii\db\Schema;
use yii\db\Migration;

class m150817_084220_create_table_parser_links extends Migration
{

    private $_table = '{{parser_links}}';

    public function init()
    {
        $this->db = 'dbSpider';
        parent::init();
    }

    public function safeUp()
    {
        $this->createTable($this->_table, [
            'id' => Schema::TYPE_PK,
            'domain_id' => Schema::TYPE_INTEGER,
            'link' => 'varchar(255) NOT NULL',
            'process_id' => 'tinyint(2) UNSIGNED DEFAULT NULL',
            'status' => 'tinyint(1) DEFAULT 0',
            ], 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=MyISAM');

        $this->createIndex('domain_id', $this->_table, 'domain_id');
        $this->createIndex('link', $this->_table, 'link');
        $this->createIndex('process_id', $this->_table, 'process_id');
        $this->createIndex('status', $this->_table, 'status');
    }

    public function safeDown()
    {
        $this->dropTable($this->_table);
    }
}
