<?php

require_once __DIR__.'/autoload.php';

use Javanile\HttpRobot\HttpRobot;

echo "[vtiger] setup wizard start...\n";

echo '[vtiger] arguments: '.DB_HOST.' '.DB_PORT.' '.DB_NAME.' '.DB_USER.' '.DB_PASS.' '.DB_ROOT."\n";
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
        '__vtrftk' => $vtrftk,
        'module' => 'Install',
        'view' => 'Index',
        'mode' => 'Step5',
        'db_type' => 'mysqli',
        'db_hostname' => DB_HOST,
        'db_username' => DB_USER,
        'db_password' => DB_PASS,
        'db_name' => DB_NAME,
        'db_root_username' => '',
        'db_root_password' => '',
        'currency_name' => 'USA, Dollars',
        'admin' => 'admin',
        'password' => 'admin',
        'retype_password' => 'admin',
        'firstname' => '',
        'lastname' => 'Administrator',
        'admin_email' => 'vtiger@localhost.lan',
        'dateformat' => 'dd-mm-yyyy',
        'timezone' => 'America/Los_Angeles',
    ],
    ['__vtrftk', 'auth_key']
);
echo "[vtiger] #2 form-token: '{$values['__vtrftk']}' auth-key: '{$values['auth_key']}'\n";

// Confirm installation
$values = $robot->post(
    'index.php',
    [
        '__vtrftk' => $values['__vtrftk'],
        'auth_key' => $values['auth_key'],
        'module' => 'Install',
        'view' => 'Index',
        'mode' => 'Step6',
    ],
    ['__vtrftk', 'auth_key']
);
echo "[vtiger] #3 form-token: '{$values['__vtrftk']}' auth-key: '{$values['auth_key']}'\n";

// Select industry sector
$vtrftk = $robot->post(
    'index.php',
    [
        '__vtrftk' => $values['__vtrftk'],
        'auth_key' => $values['auth_key'],
        'module' => 'Install',
        'view' => 'Index',
        'mode' => 'Step7',
        'industry' => 'Accounting',
    ],
    ['__vtrftk']
);

// First login
$vtrftk = $robot->post(
    'index.php?module=Users&action=Login',
    [
        '__vtrftk' => $vtrftk,
        'username' => 'admin',
        'password' => 'admin',
    ],
    ['__vtrftk']
);

// Setup crm modules
$vtrftk = $robot->post(
    'index.php?module=Users&action=SystemSetupSave',
    [
        '__vtrftk' => $vtrftk,
        'packages[Tools]' => 'on',
        'packages[Sales]' => '',
        'packages[Marketing]' => '',
        'packages[Support]' => '',
        'packages[Inventory]' => '',
        'packages[Project]' => '',
    ],
    ['__vtrftk']
);

// Save user settings
$vtrftk = $robot->post(
    'index.php?module=Users&action=UserSetupSave',
    [
        '__vtrftk' => $vtrftk,
        'currency_name' => 'Euro',
        'lang_name' => 'it_it',
        'time_zone' => 'Europe/Amsterdam',
        'date_format' => 'dd-mm-yyyy',
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