<?php
use yii\helpers\Html;

/* @var $this \yii\web\View view component instance */
/* @var $message \yii\mail\MessageInterface the message being composed */
/* @var $content string main view render result */
?>
<?php $this->beginPage() ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?= Yii::$app->charset ?>" />
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
    <p><b>Total proxies blocked : </b> <?=$count_total;?></p>
    <hr>
    <table border="1">
        <tr>
            <td>IP</td>
            <td>Google</td>
            <td>Yandex</td>
            <td>Yahoo</td>
            <td>Bing</td>
        </tr>

        <? 
            $total = array('google' => 0, 'yandex' => 0, 'yahoo'=> 0, 'bing' => 0);
            foreach($data as $proxy):?>
            <tr>
                <td style="text-align: center;"><?= $proxy['ip'];?></td>
                <td style="text-align: center;"><?= $proxy['google']; if($proxy['google'] != 0) $total['google']++;?></td>
                <td style="text-align: center;"><?= $proxy['yandex']; if($proxy['yandex'] != 0) $total['yandex']++;?></td>
                <td style="text-align: center;"><?= $proxy['yahoo']; if($proxy['yahoo'] != 0) $total['yahoo']++;?></td>
                <td style="text-align: center;"><?= $proxy['bing']; if($proxy['bing'] != 0) $total['bing']++;?></td>
                <td style="text-align: center;"><?= $proxy['total'];?></td>
                <td style="text-align: center;">scope: <?= $proxy['scope'];?></td>
            </tr>
        <? endforeach;?>
            <tr>
                <td></td>
                <td style="text-align: center;"><?= $total['google'];?></td>
                <td style="text-align: center;"><?= $total['yandex'];?></td>
                <td style="text-align: center;"><?= $total['yahoo'];?></td>
                <td style="text-align: center;"><?= $total['bing'];?></td>
                <td></td>
            </tr>
    </table>
</body>
</html>
<?php $this->endPage() ?>
