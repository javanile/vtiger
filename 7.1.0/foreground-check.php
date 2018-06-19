<?php
/**
 * foreground-check.php
 *
 * Check database before start container
 *
 * @category   CategoryName
 * @package    PackageName
 * @author     Francesco Bianco
 * @copyright  2018 Javanile.org
 */

define('MYSQL_HOST', getenv('MYSQL_HOST'));
define('MYSQL_DATABASE', getenv('MYSQL_DATABASE'));
define('MYSQL_ROOT_PASSWORD', getenv('MYSQL_ROOT_PASSWORD'));
define('MYSQL_USER', getenv('MYSQL_USER'));
define('MYSQL_PASSWORD', getenv('MYSQL_PASSWORD'));

/**
 * @param $user
 * @param $password
 * @return mysqli
 */
function db_connect($user, $password)
{
    return @mysqli_connect(MYSQL_HOST, $user, $password);
}

/**
 * @param $link
 * @return array|null
 */
function db_exists($link)
{
    $name = MYSQL_DATABASE;

    return @mysqli_fetch_assoc(@mysqli_query($link, "SHOW DATABASES LIKE '{$name}'"));
}

/**
 * @param $link
 * @return bool
 */
function db_is_empty($link)
{
    return !@mysqli_fetch_assoc(@mysqli_query($link, "SHOW TABLES"));
}

/**
 * @param $link
 * @return bool|mysqli_result
 */
function db_create($link)
{
    $name = MYSQL_DATABASE;

    return mysqli_query($link, "CREATE DATABASE {$name} CHARACTER SET utf8 COLLATE utf8_general_ci");
}

// process standard mysql user
if (MYSQL_USER && MYSQL_PASSWORD) {
    // first attempt avoid database delay
    if (!db_connect(MYSQL_USER, MYSQL_PASSWORD)) {
        sleep(5);
    }

    // second attempt real check
    if ($link = db_connect(MYSQL_USER, MYSQL_PASSWORD)) {
        if (db_exists($link)) {
            if (db_is_empty($link)) {
                die('MYSQL_IMPORT_BY_USER');
            } else {
                die('READY');
            }
        }
    }
}

// process root mysql use
if (MYSQL_ROOT_PASSWORD) {
    // first attempt avoid database delay
    if (!db_connect('root', MYSQL_ROOT_PASSWORD)) {
        sleep(5);
    }

    // second attempt real check
    if ($link = db_connect('root', MYSQL_ROOT_PASSWORD)) {
        if (db_exists($link)) {
            if (db_is_empty($link)) {
                die('MYSQL_IMPORT_BY_ROOT');
            } else {
                die('READY');
            }
        } elseif (db_create($link)) {
            die('MYSQL_IMPORT_BY_ROOT');
        } else {
            die('MYSQL_QUERY_ERROR_'.mysqli_errno($link));
        }
    } else {
        die('MYSQL_ROOT_CONNECT_'.mysqli_connect_errno());
    }
} else {
    die('MYSQL_ROOT_PASSWORD_MISSING');
}
