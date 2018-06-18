<?php
date_default_timezone_set('America/Los_Angeles');

require_once __DIR__.'/vendor/autoload.php';

$client = new GuzzleHttp\Client([
    'base_uri' => 'http://localhost/',
    'cookies' => true,
]);

function ($method, $path, $requests, $returns) use ($client) {
    $response = $client->request($method, $path);
    $html = $response->getBody()->getContents();
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML($html);
    $xpath = new DOMXPath($doc);
    $vtrftk = $xpath->query('//input[@name="__vtrftk"]/@value')->item(0)->nodeValue;
}


$response = $client->request('GET', 'index.php?module=Install&view=Index&mode=Step4');
$html = $response->getBody()->getContents();
$doc = new DOMDocument();
libxml_use_internal_errors(true);
$doc->loadHTML($html);
$xpath = new DOMXPath($doc);
$vtrftk = $xpath->query('//input[@name="__vtrftk"]/@value')->item(0)->nodeValue;

$response = $client->request('POST', 'index.php', [
    'form_params' => [
        '__vtrftk' => $vtrftk,
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
]);
$html = $response->getBody()->getContents();
$doc = new DOMDocument();
libxml_use_internal_errors(true);
$doc->loadHTML($html);
$xpath = new DOMXPath($doc);
$vtrftk = $xpath->query('//input[@name="__vtrftk"]/@value')->item(0)->nodeValue;
$authKey = $xpath->query('//input[@name="auth_key"]/@value')->item(0)->nodeValue;

$response = $client->request('POST', 'index.php', [
    'form_params' => [
        '__vtrftk' => $vtrftk,
        'auth_key' => $authKey,
        'module' => 'Install',
        'view' => 'Index',
        'mode' => 'Step6',
    ]
]);
$html = $response->getBody()->getContents();
$doc = new DOMDocument();
libxml_use_internal_errors(true);
$doc->loadHTML($html);
$xpath = new DOMXPath($doc);
$vtrftk = $xpath->query('//input[@name="__vtrftk"]/@value')->item(0)->nodeValue;
$authKey = $xpath->query('//input[@name="auth_key"]/@value')->item(0)->nodeValue;

$response = $client->request('POST', 'index.php', [
    'form_params' => [
        '__vtrftk' => $vtrftk,
        'auth_key' => $authKey,
        'module' => 'Install',
        'view' => 'Index',
        'mode' => 'Step7',
        'industry' => 'Accounting',
    ]
]);
$html = $response->getBody()->getContents();
$doc = new DOMDocument();
libxml_use_internal_errors(true);
$doc->loadHTML($html);
$xpath = new DOMXPath($doc);
$vtrftk = $xpath->query('//input[@name="__vtrftk"]/@value')->item(0)->nodeValue;

$response = $client->request('POST', 'index.php?module=Users&action=Login', [
    'form_params' => [
        '__vtrftk' => $vtrftk,
        'username' => 'admin',
        'password' => 'admin',
    ]
]);
$html = $response->getBody()->getContents();
$doc = new DOMDocument();
libxml_use_internal_errors(true);
$doc->loadHTML($html);
$xpath = new DOMXPath($doc);
$vtrftk = $xpath->query('//input[@name="__vtrftk"]/@value')->item(0)->nodeValue;

$response = $client->request('POST', 'index.php?module=Users&action=SystemSetupSave', [
    'form_params' => [
        '__vtrftk' => $vtrftk,
        'packages[Marketing]' => '1',
    ]
]);
$html = $response->getBody()->getContents();
$doc = new DOMDocument();
libxml_use_internal_errors(true);
$doc->loadHTML($html);
$xpath = new DOMXPath($doc);
$vtrftk = $xpath->query('//input[@name="__vtrftk"]/@value')->item(0)->nodeValue;

$response = $client->request('POST', 'index.php?module=Users&action=UserSetupSave', [
    'form_params' => [
        '__vtrftk' => $vtrftk,
        'currency_name' => 'Euro',
        'lang_name' => 'it_it',
        'time_zone' => 'Europe/Amsterdam',
        'date_format' => 'dd-mm-yyyy',
    ]
]);
$html = $response->getBody()->getContents();
