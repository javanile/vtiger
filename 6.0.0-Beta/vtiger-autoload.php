<?php

define('DB_HOST', $_ENV['MYSQL_HOST'] ?: 'mysql');
define('DB_PORT', $_ENV['MYSQL_PORT'] ?: '3306');
define('DB_NAME', $_ENV['MYSQL_DATABASE'] ?: 'vtiger');
define('DB_USER', $_ENV['MYSQL_USER'] ?: 'root');
define('DB_PASS', $_ENV['MYSQL_PASSWORD'] ?: ($_ENV['MYSQL_ROOT_PASSWORD'] ?: 'root'));
define('DB_ROOT', $_ENV['MYSQL_ROOT_PASSWORD'] ?: 'root');

date_default_timezone_set('America/Los_Angeles');

require_once __DIR__.'/vendor/autoload.php';
