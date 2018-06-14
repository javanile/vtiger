<?php

$MYSQL_ROOT_PASSWORD = getenv('MYSQL_ROOT_PASSWORD');
if (!$MYSQL_ROOT_PASSWORD) {
    die('ERROR_MYSQL_ROOT_PASSWORD_MISSING');
}

$MYSQL_HOST = getenv('MYSQL_HOST') ?: 'mysql';
$MYSQL_DATABASE = getenv('MYSQL_DATABASE') ?: 'vtigercrm';
$MYSQL_USER = getenv('MYSQL_USER');
$MYSQL_PASSWORD = getenv('MYSQL_PASSWORD');

$link = @mysqli_connect($MYSQL_HOST, 'root', $MYSQL_ROOT_PASSWORD);
if (!$link) {
    $error = mysqli_connect_errno();
    die('ERROR_MYSQL_CONNECT_'.$error);
}

$db = mysqli_fetch_assoc(mysqli_query($link, "SHOW DATABASES LIKE '{$MYSQL_DATABASE}'"));
if (!$db) {
    if (!mysqli_query($link, "CREATE DATABASE {$MYSQL_DATABASE}")) {
        die('ERROR_MYSQL_QUERY_'.mysqli_errno($link));
    } else {
        die('IMPORT_DB');
    }
}

$table = mysqli_fetch_assoc(mysqli_query($link, "SHOW TABLES LIKE 'vtiger_users'"));
if (!$table) {
    die('IMPORT_DB');
}

echo 'READY';
