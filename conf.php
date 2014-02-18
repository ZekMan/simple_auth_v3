<?php
//~ Старт сессии, файл должен быть сохранен в UTF8 без DOM информации
session_start();

include_once 'module.php';

//~ Параметры потключения к бд
$db_host = 'localhost';
$db_login = 'test';
$db_passwd = 'test';
$db_name = 'test';

DEFINE("SECRET_KEY", "LOL");
mb_internal_encoding("UTF-8");

// подключаемся к бд
mysql::connect($db_host, $db_login, $db_passwd, $db_name);
?>
