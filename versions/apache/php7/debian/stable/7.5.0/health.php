<?php

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'vtiger');
define('DB_USER', 'root');
define('DB_PASS', 'root');

date_default_timezone_set('UTC');

if (isset($_GET['setcookie']) && $_GET['setcookie'] && isset($_POST['cookie_name']) && isset($_POST['cookie_value'])) {
    setcookie($_POST['cookie_name'], $_POST['cookie_value'], time() + 3600);
}

if (function_exists('mysql_connect')) {
    try {
        @mysql_connect(DB_HOST, DB_USER, DB_PASS);
        $mysql = @mysql_error();
    } catch (\Exception $error) {
        $mysql = $error->getMessage();
    }
} else {
    $mysql = 'Extension not installed';
}

if (function_exists('mysqli_connect')) {
    try {
        mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        $mysqli = mysqli_connect_error();
    } catch (\Exception $error) {
        $mysqli = $error->getMessage();
    }
} else {
    $mysqli = 'Extension not installed';
}

$response = [
    'status' => 'OK',
    'cookie' => $_COOKIE,
    'database' => [
        'user' => DB_USER,
        'mysql' => $mysql ?: 'OK',
        'mysqli' => $mysqli ?: 'OK',
    ]
];

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";
