<?php

define('DB_HOST', getenv('MYSQL_HOST') ?: 'mysql');
define('DB_PORT', getenv('MYSQL_PORT') ?: '3306');
define('DB_NAME', getenv('MYSQL_DATABASE') ?: 'vtiger');
define('DB_USER', getenv('MYSQL_USER') ?: 'root');
define('DB_PASS', getenv('MYSQL_PASSWORD') ?: (getenv('MYSQL_ROOT_PASSWORD') ?: 'root'));
define('DB_ROOT', getenv('MYSQL_ROOT_PASSWORD') ?: 'root');

date_default_timezone_set('America/Los_Angeles');

require_once '/root/.composer/vendor/autoload.php';
