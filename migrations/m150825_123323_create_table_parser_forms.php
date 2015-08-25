<?php
use yii\db\Schema;
use yii\db\Migration;

class m150825_123323_create_table_parser_forms extends Migration
{
    private $_table = '{{parser_forms}}';

    public function init()
    {
        $this->db = 'dbSpider';
        parent::init();
    }

    public function safeUp()
    {
        $this->createTable($this->_table, [
            'id' => Schema::TYPE_PK,
            'domain_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'link' => 'varchar(255) NOT NULL',
            'form_id' => Schema::TYPE_INTEGER . ' DEFAULT NULL', 
            'processed' => 'tinyint(1) NOT NULL DEFAULT 0',
            'create_date' => 'DATETIME DEFAULT NULL',
            ], 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=MyISAM');
        
        $this->createIndex('domain_id', $this->_table, 'domain_id');
        $this->createIndex('link', $this->_table, 'link(50)');
        $this->createIndex('processed', $this->_table, 'processed');
    }

    public function safeDown()
    {
        $this->dropTable($this->_table);
    }
}
