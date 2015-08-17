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
            'link' => 'varchar(255) NOT NULL',
            'status' => 'tinyint(1) DEFAULT 0',
            ], 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=MyISAM');

        $this->createIndex('link', $this->_table, 'link');
        $this->createIndex('status', $this->_table, 'status');
    }

    public function safeDown()
    {
        $this->dropTable($this->_table);
    }
}
