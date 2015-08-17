<?php
define('APP_PATH', dirname(__FILE__)); //项目目录
$app = new Yaf_Application(APP_PATH . '/conf/app.ini');
$app->bootstrap()->run(); //call bootstrap methods defined in Bootstrap.php
?>