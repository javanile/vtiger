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

//
$values = GET('index.php?module=Install&view=Index&mode=Step4', ['__vtrftk']);

//
$values = POST(
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

// confirm installation
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

// select industry sector
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

// first login
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

// setup crm modules
$values = POST(
    $client,
    'index.php?module=Users&action=SystemSetupSave',
    ['__vtrftk'],
    [
        '__vtrftk' => $values['__vtrftk'],
        'packages[Marketing]' => '1',
    ]
);

// save user settings
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
