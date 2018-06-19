<?php
libxml_use_internal_errors(true);
date_default_timezone_set('America/Los_Angeles');

require_once __DIR__.'/vendor/autoload.php';

$client = new GuzzleHttp\Client([
    'base_uri' => 'http://localhost/',
    'cookies' => true,
]);

/**
 * @param $client
 * @param $path
 * @param null $returns
 * @return array
 */
function GET($client, $path, $returns = null)
{
    $response = $client->request('GET', $path);

    return VALUES($response->getBody()->getContents(), $returns);
}

/**
 * @param $client
 * @param $path
 * @param null $returns
 * @return array
 */
function POST($client, $path, $returns = null, $params = null)
{
    $response = $client->request('POST', $path, [ 'form_params' => $params ]);

    return VALUES($response->getBody()->getContents(), $returns);
}

/**
 * @param $html
 * @param $returns
 * @return array
 */
function VALUES($html, $returns)
{
    $doc = new DOMDocument();
    $doc->loadHTML($html);
    $xpath = new DOMXPath($doc);

    $returnValues = [];
    foreach ($returns as $key) {
        $returnValues[$key] = $xpath->query('//input[@name="'.$key.'"]/@value')->item(0)->nodeValue;
    }

    return $returnValues;
}

// Get session token
$values = GET($client, 'index.php?module=Install&view=Index&mode=Step4', ['__vtrftk']);

// Submit installation params
$values = POST(
    $client,
    'index.php',
    ['__vtrftk', 'auth_key'],
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
    ]
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
