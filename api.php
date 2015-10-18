<?php
define('APP_PATH', dirname(__FILE__));
header('Access-Control-Allow-Origin:http://front.yunyin.org');
header('Access-Control-Allow-Credentials:true');
header('Access-Control-Allow-Methods:GET,POST,PUT,DELETE');
header('Access-Control-Allow-Headers:X-Requested-With,Token');

$app = new Yaf_Application(APP_PATH . '/conf/app.ini');
$app->run();
?>