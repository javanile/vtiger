<?php
/**
 * Vtiger Installation Script.
 *
 * @author Francesco Bianco <bianco@javanile.org>
 */

date_default_timezone_set('America/Los_Angeles');

define('VT_VERSION', getenv('VT_VERSION'));

if (version_compare(VT_VERSION, '7.0.0', '>=')) {
    define('DB_TYPE', 'mysqli');
    define('DB_HOST', '127.0.0.1');
    define('DB_PORT', '3306');
    define('DB_NAME', 'vtiger');
    define('DB_USER', 'vtiger');
    define('DB_PASS', 'vtiger');
    define('DB_ROOT', '');
} elseif (version_compare(VT_VERSION, '6.0.0', '>=') && version_compare(VT_VERSION, '7.0.0', '<')) {
    define('DB_TYPE', 'mysql');
    define('DB_HOST', '127.0.0.1');
    define('DB_PORT', '3306');
    define('DB_NAME', 'vtiger');
    define('DB_USER', 'root');
    define('DB_PASS', 'root');
    define('DB_ROOT', 'root');
} else {
    echo "[vtiger] Error unsupported version.";
    exit(1);
}

require_once '/usr/src/vtiger/vendor/autoload.php';

use Javanile\HttpRobot\HttpRobot;

echo "[vtiger] Testing installation...\n";

echo '[vtiger] Database params: '.DB_TYPE.' '.DB_HOST.' '.DB_PORT.' '.DB_NAME.' '.DB_USER.' '.DB_PASS."\n";
$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
if (mysqli_connect_errno()) {
    echo '[vtiger] Database error: '.mysqli_connect_errno().' - '.mysqli_connect_error()."\n";
    exit(1);
}

$robot = new HttpRobot([
    'base_uri' => 'http://localhost/',
    'cookies'  => true,
]);

// Check if cookie are working...
$data = $robot->get('health.php', ['@html']);
$data = $robot->post('health.php?setcookie=yes', ['cookie_name' => 'test', 'cookie_value' => '1234'], ['@html']);
$data = $robot->get('health.php', ['@html']);

/**
 * Get session token
 */
echo "[vtiger] (#1) Get session token";
$values = $robot->get('index.php?module=Install&view=Index&mode=Step4', ['__vtrftk', '@text']);
echo " -> token: '{$values['__vtrftk']}'\n";
if (version_compare(VT_VERSION, '7.0.0', '>=')) {
    if (empty($values['__vtrftk'])) {
        echo " -> [ERROR] Session token not found\n";
        echo $values['@text'];
        exit(1);
    }
}

/**
 * Submit installation params
 */
echo "[vtiger] (#2) Sending installation parameters";
$values = $robot->post(
    'index.php',
    [
        '__vtrftk'         => $values['__vtrftk'],
        'module'           => 'Install',
        'view'             => 'Index',
        'mode'             => 'Step5',
        'db_type'          => DB_TYPE,
        'db_hostname'      => DB_HOST,
        'db_username'      => DB_USER,
        'db_password'      => DB_PASS,
        'db_name'          => DB_NAME,
        'db_root_username' => DB_ROOT,
        'db_root_password' => DB_ROOT,
        'currency_name'    => 'USA, Dollars',
        'admin'            => 'admin',
        'password'         => 'admin',
        'retype_password'  => 'admin',
        'firstname'        => '',
        'lastname'         => 'Administrator',
        'admin_email'      => 'vtiger@localhost.lan',
        'dateformat'       => 'dd-mm-yyyy',
        'timezone'         => 'America/Los_Angeles',
    ],
    ['__vtrftk', 'auth_key', '@text']
);
echo " -> form-token: '{$values['__vtrftk']}' auth-key: '{$values['auth_key']}'\n";

echo "[vtiger] (#3) Confirm installation parameters";
$values = $robot->post(
    'index.php',
    [
        '__vtrftk' => $values['__vtrftk'],
        'auth_key' => $values['auth_key'],
        'module'   => 'Install',
        'view'     => 'Index',
        'mode'     => 'Step6',
    ],
    ['__vtrftk', 'auth_key', '@text']
);
echo " -> form-token: '{$values['__vtrftk']}' auth-key: '{$values['auth_key']}'\n";

/**
 * Selecting industry
 */
echo "[vtiger] (#4) Selecting industry";
$values = $robot->post(
    'index.php',
    [
        '__vtrftk' => $values['__vtrftk'],
        'auth_key' => $values['auth_key'],
        'module'   => 'Install',
        'view'     => 'Index',
        'mode'     => 'Step7',
        'industry' => 'Accounting',
    ],
    ['__vtrftk', 'auth_key', '@text']
);
echo " -> form-token: '{$values['__vtrftk']}' auth-key: '{$values['auth_key']}'\n";
if (version_compare(VT_VERSION, '7.0.0', '>=')) {
    if (empty($values['__vtrftk'])) {
        echo " -> [ERROR] install error on industry selector\n";
        $mysqli = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        $error = mysqli_connect_error();
        $result = mysqli_query($mysqli, "SHOW TABLES");
        while ($table = mysqli_fetch_row($result))  {
            echo "Table: $table[0]\n";
        }
        echo $values['@text'];
        if (file_exists('/var/lib/vtiger/logs/php.log')) {
            echo file_get_contents('/var/lib/vtiger/logs/php.log');
        }
        #exit(1);
    }
}

/**
 * First login seems required only for >7
 */
echo "[vtiger] (#5) First login";
$values = $robot->post(
    'index.php?module=Users&action=Login',
    [
        '__vtrftk' => $values['__vtrftk'],
        'username' => 'admin',
        'password' => 'admin',
    ]
    //,
    //['__vtrftk', '@text']
);
if (version_compare(VT_VERSION, '7.0.0', '>=')) {
    if (empty($values['__vtrftk'])) {
        echo " -> [ERROR] install error on first login.\n";
        #echo $values['@text'];
        echo file_get_contents('/var/www/html/logs/php.log');
        #exit(1);
    }
}
echo " -> form-token: '{$values['__vtrftk']}' auth-key: '{$values['auth_key']}'\n";

/**
 * Select crm modules
 */
echo "[vtiger] (#6) Select modules and packages";
$values = $robot->post(
    'index.php?module=Users&action=SystemSetupSave',
    [
        '__vtrftk'            => $values['__vtrftk'],
        'packages[Tools]'     => 'on',
        'packages[Sales]'     => 'on',
        'packages[Marketing]' => 'on',
        'packages[Support]'   => 'on',
        'packages[Inventory]' => 'on',
        'packages[Project]'   => 'on',
    ],
    ['__vtrftk', '@text']
);
echo " -> form-token: '{$values['__vtrftk']}' auth-key: '{$values['auth_key']}'\n";

// Save user settings
echo "[vtiger] (#7) Save user settings";
$values = $robot->post(
    'index.php?module=Users&action=UserSetupSave',
    [
        '__vtrftk'      => $values['__vtrftk'],
        'currency_name' => 'Euro',
        'lang_name'     => 'en_us',
        'time_zone'     => 'Europe/Amsterdam',
        'date_format'   => 'dd-mm-yyyy',
    ],
    ['__vtrftk', '@text']
);
echo " -> form-token: '{$values['__vtrftk']}' auth-key: '{$values['auth_key']}'\n";

// =================================================================
// Select Modules
/*
$modules = [
    'Documents' => false,
];
foreach ($modules as $module => $status) {
    echo "[vtiger] ".($status?'enable':'disable')." module '${module}': ";
    $resp = $robot->post(
        'index.php',
        [
            '__vtrftk' => $vtrftk,
            'module' => 'ModuleManager',
            'parent' => 'Settings',
            'action' => 'Basic',
            'mode' => 'updateModuleStatus',
            'forModule' => $module,
            'updateStatus' => $status,
        ]
    );
    echo trim($resp)."\n";
}
*/
