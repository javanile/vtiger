--TEST--
Vanilla testing session write and read
--FILE--
<?php
$_tmp = dirname(__FILE__) . '/tmp';
$_id  = 1234;

if (!file_exists($_tmp)) {
    mkdir($_tmp);
}
ini_set('session.save_path', $_tmp);
session_id($_id);

session_start();

$nCount = 0;
while(++$nCount <= 2) {
    if (!isset($_SESSION['test'])) {
        var_dump("Setting..");
        $_SESSION['test'] = 'foobar';
    } else {
        var_dump("Retrieving..");
        var_dump($_SESSION['test']);
    }
}
--CLEAN--
<?php
$_tmp = dirname(__FILE__) . '/tmp';
$_id  = 1234;

unlink($_tmp . '/' . 'sess_' . $_id);
rmdir($_tmp);
--EXPECT--
string(9) "Setting.."
string(12) "Retrieving.."
string(6) "foobar"

