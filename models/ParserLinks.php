<?php

namespace maxlen\parser\models;

use Yii;

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
            [['status'], 'integer'],
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
            'link' => 'Link',
            'status' => 'Status',
        ];
    }
}
