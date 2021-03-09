<?php

define('VT_VERSION', getenv('VT_VERSION'));

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'vtiger');
define('DB_USER', 'root');
define('DB_PASS', 'root');

date_default_timezone_set('America/Los_Angeles');

require_once '/usr/src/vtiger/vendor/autoload.php';

use Javanile\HttpRobot\HttpRobot;

echo "[vtiger] vtiger test installation...\n";

echo '[vtiger] arguments: '.DB_HOST.' '.DB_PORT.' '.DB_NAME.' '.DB_USER.' '.DB_PASS."\n";
$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
if (mysqli_connect_errno()) {
    echo '[vtiger] database error: '.mysqli_connect_errno().' - '.mysqli_connect_error()."\n";
    exit(1);
}

$robot = new HttpRobot([
    'base_uri' => 'http://localhost/',
    'cookies'  => true,
]);

$data = $robot->get('health.php', ['@html']);
$data = $robot->post('health.php?setcookie=yes', ['cookie_name' => 'test', 'cookie_value' => '1234'], ['@html']);
$data = $robot->get('health.php', ['@html']);

/**
 * Get session token
 */
echo "[vtiger] (#1) Get session token\n";
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
echo "[vtiger] (#2) Sending installation parameters\n";
$values = $robot->post(
    'index.php',
    [
        '__vtrftk'         => $vtrftk,
        'module'           => 'Install',
        'view'             => 'Index',
        'mode'             => 'Step5',
        'db_type'          => 'mysqli',
        'db_hostname'      => DB_HOST,
        'db_username'      => DB_USER,
        'db_password'      => DB_PASS,
        'db_name'          => DB_NAME,
        'db_root_username' => 'root',
        'db_root_password' => 'root',
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

echo "[vtiger] (#3) Confirm installation parameters\n";
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
echo "[vtiger] (#4) Selecting industry\n";
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
    ['__vtrftk', '@text']
);
echo " -> form-token: '{$values['__vtrftk']}' auth-key: '{$values['auth_key']}'\n";
if (version_compare(VT_VERSION, '7.0.0', '>=')) {
    if (empty($values['__vtrftk'])) {
        echo " -> [ERROR] install error on industry selector\n";
        echo $values['@text'];
        exit(1);
    }
}

/**
 * First login seems required only for >7
 */
echo "[vtiger] (#5) First login\n";
$values = $robot->post(
    'index.php?module=Users&action=Login',
    [
        '__vtrftk' => $values['__vtrftk'],
        'username' => 'admin',
        'password' => 'admin',
    ],
    ['__vtrftk', '@text']
);
if (version_compare(VT_VERSION, '7.0.0', '>=')) {
    if (empty($values['__vtrftk'])) {
        echo " -> [ERROR] install error on first login.\n";
        echo $values['@text'];
        exit(1);
    }
}
echo $values['@text'];
exit(1);


/**
 * Select crm modules
 */
echo "[vtiger] (#6) Select modules and packages\n";
$values = $robot->post(
    'index.php?module=Users&action=SystemSetupSave',
    [
        '__vtrftk'            => $vtrftk,
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
echo $values['@text'];
exit(1);


// Save user settings
echo "[vtiger] (#7) Save user settings\n";
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

echo $values['@text'];
exit(1);

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
