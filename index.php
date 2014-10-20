<?php

date_default_timezone_set('Europe/Belgrade');

$dir = dirname(__FILE__);
//$yii = $dir . '/../yii-1.1.13.e9e4a0/framework/yii.php'; 

//$yii = $dir . '/../yii-1.1.14.f0fee9/framework/yii.php'; 

$yii = $dir . '/../yii-1.1.12.b600af/framework/yii.php';

$config = $dir . "/protected/config/main.php";
// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);
// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);

require_once($yii);  
require_once($dir . '/protected/components/Aplikacija.php');

Yii::createApplication('Aplikacija', $config)->run();
?>