<?php
date_default_timezone_set('America/Los_Angeles');

require_once __DIR__.'/vendor/autoload.php';

use Javanile\HttpRobot\HttpRobot;

// Get session token
$robot = new HttpRobot([
    'base_uri' => 'http://localhost/',
    'cookies'  => true,
]);

// Get session token
$vtrftk = $robot->get('index.php?module=Install&view=Index&mode=Step4', '__vtrftk');

// Submit installation params
$values = POST(
    $client,
    'index.php',
    [
        '__vtrftk' => $values['__vtrftk'],
        'module' => 'Install',
        'view' => 'Index',
        'mode' => 'Step5',
        'db_type' => 'mysqli',
        'db_hostname' => '127.0.0.1',
        'db_username' => 'root',
        'db_password' => 'root',
        'db_name' => 'vtigercrm',
        'db_root_username' => '',
        'db_root_password' => '',
        'currency_name' => 'USA, Dollars',
        'admin' => 'admin',
        'password' => 'admin',
        'retype_password' => 'admin',
        'firstname' => '',
        'lastname' => 'Administrator',
        'admin_email' => 'info@javanile.org',
        'dateformat' => 'dd-mm-yyyy',
        'timezone' => 'America/Los_Angeles',
    ],
    ['__vtrftk', 'auth_key']
);

// Confirm installation
$values = POST(
    $client,
    'index.php',
    ['__vtrftk', 'auth_key'],
    [
        '__vtrftk' => $values['__vtrftk'],
        'auth_key' => $values['auth_key'],
        'module' => 'Install',
        'view' => 'Index',
        'mode' => 'Step6',
    ]
);

// Select industry sector
$values = POST(
    $client,
    'index.php',
    ['__vtrftk'],
    [
        '__vtrftk' => $values['__vtrftk'],
        'auth_key' => $values['auth_key'],
        'module' => 'Install',
        'view' => 'Index',
        'mode' => 'Step7',
        'industry' => 'Accounting',
    ]
);

// First login
$values = POST(
    $client,
    'index.php?module=Users&action=Login',
    ['__vtrftk'],
    [
        '__vtrftk' => $values['__vtrftk'],
        'username' => 'admin',
        'password' => 'admin',
    ]
);

// Setup crm modules
$values = POST(
    $client,
    'index.php?module=Users&action=SystemSetupSave',
    ['__vtrftk'],
    [
        '__vtrftk' => $values['__vtrftk'],
        'packages[Tools]' => 'on',
        'packages[Sales]' => '',
        'packages[Marketing]' => '',
        'packages[Support]' => '',
        'packages[Inventory]' => '',
        'packages[Project]' => '',
    ]
);

// Save user settings
$values = POST(
    $client,
    'index.php?module=Users&action=UserSetupSave',
    ['__vtrftk'],
    [
        '__vtrftk' => $values['__vtrftk'],
        'currency_name' => 'Euro',
        'lang_name' => 'it_it',
        'time_zone' => 'Europe/Amsterdam',
        'date_format' => 'dd-mm-yyyy',
    ]
);
