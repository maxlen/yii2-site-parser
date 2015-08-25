<?php

namespace maxlen\parser\models;

use Yii;

/**
 * This is the model class for table "parser_forms".
 *
 * @property integer $id
 * @property integer $domain_id
 * @property string $link
 * @property integer $form_id
 * @property integer $processed
 * @property string $create_date
 */
class ParserForms extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'parser_forms';
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
            [['domain_id', 'link'], 'required'],
            [['domain_id', 'form_id', 'processed'], 'integer'],
            [['create_date'], 'safe'],
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
            'domain_id' => 'Domain ID',
            'link' => 'Link',
            'form_id' => 'Form ID',
            'processed' => 'Processed',
            'create_date' => 'Create Date',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDomain()
    {
        return $this->hasOne(ParserDomains::className(), ['id' => 'domain_id']);
    }
    
    public static function createForm($domainId, $link) {
        $form = new self;
        $form->domain_id = $domainId;
        $form->link = $link;
        $form->create_date = date('Y-m-d H:i:s');
        $form->save();
    }
}
