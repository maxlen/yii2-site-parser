<?php

namespace maxlen\parser\models;

use Yii;
use maxlen\parser\helpers\Parser;

/**
 * This is the model class for table "parser_links".
 *
 * @property integer $id
 * @property string $link
 * @property integer $status
 */
class ParserLinks extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'parser_links';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('dbSpider');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['link'], 'required'],
            ['link', 'unique'],
            [['status', 'domain_id'], 'integer'],
            [['link'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'domain_id' => 'Domain',
            'link' => 'Link',
            'status' => 'Status',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDomain()
    {
        return $this->hasOne(ParserDomains::className(), ['id' => 'domain_id']);
    }
    
    public static function clearTable() {
        self::getDb()->createCommand()->truncateTable(self::tableName(true))->execute();
    }
    
    public static function setAsBeginAndGet($processId)
    {
        self::getDb()->createCommand(
            'UPDATE ' . self::tableName() . ' SET status = ' . Parser::TYPE_PROCESS
            . ', process_id = ' . $processId
            . ' WHERE status = ' . Parser::TYPE_NOT_PARSED . ' LIMIT 1'
        )->execute();
        
        return self::find()->where(['status' => Parser::TYPE_PROCESS, 'process_id' => $processId])->limit(1)->one();
    }
    
    public static function cleanNotFinished()
    {
        self::getDb()->createCommand(
            'UPDATE ' . self::tableName() . ' SET status = ' . Parser::TYPE_NOT_PARSED
            . ', process_id = NULL '
            . ' WHERE status = ' . Parser::TYPE_PROCESS
        )->execute();
    }
}
