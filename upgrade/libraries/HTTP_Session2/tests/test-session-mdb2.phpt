--TEST--
HTTP_Session2 with MDB2 container (and sqlite) write and read
--SKIPIF--
<?php
if (
    false === @include_once 'MDB2.php'
    || false === @include_once 'MDB2/Driver/sqlite.php'
) {
    die('skip Please install MDB2 (and its SQLite driver) to run this test.');
}
if (!extension_loaded('sqlite')) {
    die('skip Please install the sqlite extension to run this test.');
}
?>
--FILE--
<?php
$_tmp = dirname(__FILE__) . '/tmp';
$_db  = $_tmp . '/test.db';

require_once 'MDB2.php';
require_once 'HTTP/Session2.php';

/**
 * This is a hack.
 *
 * @param string $_db Path to the db.
 *
 * @return void
 */
function createDB($db)
{
    if (!file_exists($db)) {
        if ($db = sqlite_open($db, "0666", $sqliteerror)) {
            // create table
            $sql  = 'CREATE TABLE "sessiondata" (';
            $sql .= '"id" VARCHAR(32) NOT NULL,';
            $sql .= '"expiry" INT UNSIGNED NOT NULL DEFAULT 0,';
            $sql .= '"data" TEXT NOT NULL,';
            $sql .= 'PRIMARY KEY ("id")';
            $sql .= ');';

            sqlite_query($db, $sql);
            sqlite_close($db);
        } else {
            die($sqliteerror);
        }
    }
}

if (!file_exists($_tmp)) {
    mkdir($_tmp);
}
createDB($_db);

try {
    HTTP_Session2::useCookies(false);
    HTTP_Session2::setContainer('MDB2',
        array('dsn' => "sqlite:///{$_db}",
            'table' => 'sessiondata'));

    HTTP_Session2::start('testSession');
    HTTP_Session2::id('sessionTest');

    $nCount = 0;
    while (++$nCount <= 2) {
        $_var = HTTP_Session2::get('test', 'bar');
        if ($_var == 'bar') {
            var_dump("Setting..");
            HTTP_Session2::set('test', 'foobar');
        } else {
            var_dump("Retrieving..");
            var_dump(HTTP_Session2::get('test'));
        }
    }
} catch (HTTP_Session2_Exception $e) {
    die($e->getMessage());
}
--CLEAN--
<?php
$_tmp = dirname(__FILE__) . '/tmp';
$_db  = $_tmp . '/test.db';
unlink($_db);

include dirname(__FILE__) . '/functions.php';
unlinkRecursive($_tmp, true);

--EXPECT--
string(9) "Setting.."
string(12) "Retrieving.."
string(6) "foobar"
