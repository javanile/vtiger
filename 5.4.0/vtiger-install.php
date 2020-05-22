<?php

define('VT_VERSION', getenv('VT_VERSION'));

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'vtiger');
define('DB_USER', 'vtiger');
define('DB_PASS', 'vtiger');

date_default_timezone_set('America/Los_Angeles');

require_once '/root/.composer/vendor/autoload.php';

use Javanile\HttpRobot\HttpRobot;

echo "[vtiger] vtiger test installation...\n";

echo '[vtiger] arguments: '.DB_HOST.' '.DB_PORT.' '.DB_NAME.' '.DB_USER.' '.DB_PASS."\n";
if (!mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT)) {
    echo '[vtiger] database: '.mysqli_connect_errno().' - '.mysqli_connect_error()."\n";
    exit(1);
}

$robot = new HttpRobot([
    'base_uri' => 'http://localhost/',
    'cookies'  => true,
]);

// Get session token
$vtrftk = $robot->get('index.php?module=Install&view=Index&mode=Step4', '__vtrftk');
echo "[vtiger] #1 form-token: '{$vtrftk}'\n";

// Submit installation params
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
        'db_root_username' => '',
        'db_root_password' => '',
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
echo "[vtiger] #2 form-token: '{$values['__vtrftk']}' auth-key: '{$values['auth_key']}'\n";

// Confirm installation
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
echo "[vtiger] #3 form-token: '{$values['__vtrftk']}' auth-key: '{$values['auth_key']}'\n";

// Select industry sector
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

if (version_compare(VT_VERSION, '7.0.0', '>=')) {
    if (!$values['__vtrftk']) {
        echo "[vtiger] install error on industry selector\n";
        echo $values['@text'];
        exit(1);
    }

    echo "[vtiger] #4 form-token: '{$values['__vtrftk']}'\n";
}

// /index.php?module=Users&parent=Settings&view=SystemSetup
// First login
$vtrftk = $robot->post(
    'index.php?module=Users&action=Login',
    [
        '__vtrftk' => $values['__vtrftk'],
        'username' => 'admin',
        'password' => 'admin',
    ],
    ['__vtrftk']
);

if (!$vtrftk) {
    echo "[vtiger] install error on first login.\n";
    echo $values['@text'];
    exit(1);
}

// Setup crm modules
$vtrftk = $robot->post(
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
    ['__vtrftk']
);

// Save user settings
$vtrftk = $robot->post(
    'index.php?module=Users&action=UserSetupSave',
    [
        '__vtrftk'      => $vtrftk,
        'currency_name' => 'Euro',
        'lang_name'     => 'en_us',
        'time_zone'     => 'Europe/Amsterdam',
        'date_format'   => 'dd-mm-yyyy',
    ],
    ['__vtrftk']
);

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
