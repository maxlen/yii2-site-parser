Yii2 site parser
===============

Site parser Yii2 extension


Instructions
===============

1) Add string in file \common\config\aliases.php :
   Yii::setAlias('vendor', dirname(dirname(__DIR__))  .'/vendor');

2) Add in file \common\config\params.php :
   "yii.migrations"=> [
        "@vendor/maxlen/yii2-site-parser/migrations",
    ],
    
3) Run migrations

4) Enjoying
