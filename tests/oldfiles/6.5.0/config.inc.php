<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): Francesco Bianco.
********************************************************************************/

// Adjust error_reporting favourable to deployment.
version_compare(PHP_VERSION, '5.5.0') <= 0 ? error_reporting(E_WARNING & ~E_NOTICE & ~E_DEPRECATED & E_ERROR) : error_reporting(E_WARNING & ~E_NOTICE & ~E_DEPRECATED & E_ERROR & ~E_STRICT); // PRODUCTION
//ini_set('display_errors','on'); version_compare(PHP_VERSION, '5.5.0') <= 0 ? error_reporting(E_WARNING & ~E_NOTICE & ~E_DEPRECATED) : error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);   // DEBUGGING
//ini_set('display_errors','on'); error_reporting(E_ALL); // STRICT DEVELOPMENT

if (getenv('VT_DEBUG') && !in_array(strtolower(getenv('VT_DEBUG')), ['false', '0'])) {
    ini_set('display_errors', 'on');
    error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
}

include 'vtigerversion.php';

// more than 8MB memory needed for graphics
// memory limit default value = 64M
ini_set('memory_limit', '512M');

// show or hide calendar, world clock, calculator, chat and CKEditor
// Do NOT remove the quotes if you set these to false!
$CALENDAR_DISPLAY = 'true';
$WORLD_CLOCK_DISPLAY = 'true';
$CALCULATOR_DISPLAY = 'true';
$CHAT_DISPLAY = 'true';
$USE_RTE = 'true';

// helpdesk support email id and support name (Example: 'support@vtiger.com' and 'vtiger support')
$HELPDESK_SUPPORT_EMAIL_ID = 'info@javanile.org';
$HELPDESK_SUPPORT_NAME = 'your-support name';
$HELPDESK_SUPPORT_EMAIL_REPLY_ID = $HELPDESK_SUPPORT_EMAIL_ID;

/* database configuration
      db_server
      db_port
      db_hostname
      db_username
      db_password
      db_name
*/

$dbconfig['db_server'] = getenv('MYSQL_HOST') ?: 'mysql';
$dbconfig['db_port'] = ':'.(getenv('MYSQL_PORT') ?: '3306');
$dbconfig['db_username'] = getenv('MYSQL_USER') ?: 'root';
$dbconfig['db_password'] = getenv('MYSQL_PASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD');
$dbconfig['db_name'] = getenv('MYSQL_DATABASE') ?: 'vtigercrm';
$dbconfig['db_type'] = getenv('MYSQL_TYPE') ?: 'mysqli';
$dbconfig['db_status'] = 'true';

// TODO: test if port is empty
// TODO: set db_hostname dependending on db_type
$dbconfig['db_hostname'] = $dbconfig['db_server'].$dbconfig['db_port'];

// log_sql default value = false
$dbconfig['log_sql'] = false;

// persistent default value = true
$dbconfigoption['persistent'] = true;

// autofree default value = false
$dbconfigoption['autofree'] = false;

// debug default value = 0
$dbconfigoption['debug'] = 0;

// seqname_format default value = '%s_seq'
$dbconfigoption['seqname_format'] = '%s_seq';

// portability default value = 0
$dbconfigoption['portability'] = 0;

// ssl default value = false
$dbconfigoption['ssl'] = false;

// TODO: looking for usage
$host_name = $dbconfig['db_hostname'];

// Update $_SERVER for reverse proxy with public domain
if (trim(getenv('VT_SITE_URL'))) {
    $_SERVER['HTTP_PORT'] = parse_url(trim(getenv('VT_SITE_URL')), PHP_URL_PORT);
    $_SERVER['HTTP_HOST'] = parse_url(trim(getenv('VT_SITE_URL')), PHP_URL_HOST);
    if (preg_match('/^https/i', trim(getenv('VT_SITE_URL')))) {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_HOST'] .= $_SERVER['HTTP_PORT'] && $_SERVER['HTTP_PORT'] != 443 ? ':'.$_SERVER['HTTP_PORT'] : '';
    } else {
        $_SERVER['HTTP_HOST'] .= $_SERVER['HTTP_PORT'] && $_SERVER['HTTP_PORT'] != 80 ? ':'.$_SERVER['HTTP_PORT'] : '';
    }
}

// Update $site_URL using VT_SITE_URL environment variable
$site_URL = 'http'.(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '').'://'.$_SERVER['HTTP_HOST'].'/';

// Store $site_URL on /tmp for system services
if ($_SERVER['HTTP_HOST']) {
    $site_URL_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'vtiger_site_URL';
    if (!file_exists($site_URL_file) || filemtime($site_URL_file) + 3600 < time()) {
        file_put_contents($site_URL_file, $site_URL);
        $port = parse_url('http://'.$_SERVER['HTTP_HOST'], PHP_URL_PORT);
        if ($_SERVER['HTTPS'] === 'on' && $port != 443) {
            file_put_contents(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'https_localhost_proxy', 'tcp-listen:'.$port.',reuseaddr,fork tcp:localhost:443');
        } elseif ($port != 80) {
            file_put_contents(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'http_localhost_proxy', 'tcp-listen:'.$port.',reuseaddr,fork tcp:localhost:80');
        }
    }
}

// url for customer portal (Example: http://vtiger.com/portal)
$PORTAL_URL = $site_URL.'/customerportal';

// root directory path
$root_directory = __DIR__.'/';

// cache direcory path
$cache_dir = 'cache/';

// tmp_dir default value prepended by cache_dir = images/
$tmp_dir = 'cache/images/';

// import_dir default value prepended by cache_dir = import/
$import_dir = 'cache/import/';

// upload_dir default value prepended by cache_dir = upload/
$upload_dir = 'cache/upload/';

// maximum file size for uploaded files in bytes also used when uploading import files
// upload_maxsize default value = 3000000
$upload_maxsize = 3145728; //3MB

// flag to allow export functionality
// 'all' to allow anyone to use exports
// 'admin' to only allow admins to export
// 'none' to block exports completely
// allow_exports default value = all
$allow_exports = 'all';

// files with one of these extensions will have '.txt' appended to their filename on upload
// upload_badext default value = php, php3, php4, php5, pl, cgi, py, asp, cfm, js, vbs, html, htm
$upload_badext = ['php', 'php3', 'php4', 'php5', 'pl', 'cgi', 'py', 'asp', 'cfm', 'js', 'vbs', 'html', 'htm', 'exe', 'bin', 'bat', 'sh', 'dll', 'phps', 'phtml', 'xhtml', 'rb', 'msi', 'jsp', 'shtml', 'sth', 'shtm'];

// full path to include directory including the trailing slash
// includeDirectory default value = $root_directory..'include/
$includeDirectory = $root_directory.'include/';

// list_max_entries_per_page default value = 20
$list_max_entries_per_page = '20';

// limitpage_navigation default value = 5
$limitpage_navigation = '5';

// history_max_viewed default value = 5
$history_max_viewed = '5';

// default_module default value = Home
$default_module = getenv('VT_DEFAULT_MODULE') ?: 'Home';

// default_action default value = index
$default_action = getenv('VT_DEFAULT_ACTION') ?: 'index';

// set default theme
// default_theme default value = blue
$default_theme = 'softed';

// show or hide time to compose each page
// calculate_response_time default value = true
$calculate_response_time = true;

// default text that is placed initially in the login form for user name
// no default_user_name default value
$default_user_name = '';

// default text that is placed initially in the login form for password
// no default_password default value
$default_password = '';

// create user with default username and password
// create_default_user default value = false
$create_default_user = false;
// default_user_is_admin default value = false
$default_user_is_admin = false;

// if your MySQL/PHP configuration does not support persistent connections set this to true to avoid a large performance slowdown
// disable_persistent_connections default value = false
$disable_persistent_connections = false;

//Master currency name
$currency_name = getenv('VT_CURRENCY_NAME') ?: 'USA, Dollars';

// default charset
// default charset default value = 'UTF-8' or 'ISO-8859-1'
$default_charset = 'UTF-8';

// default language
// default_language default value = en_us
$default_language = 'en_us';

// add the language pack name to every translation string in the display.
// translation_string_prefix default value = false
$translation_string_prefix = false;

//Option to cache tabs permissions for speed.
$cache_tab_perms = true;

//Option to hide empty home blocks if no entries.
$display_empty_home_blocks = false;

//Disable Stat Tracking of vtiger CRM instance
$disable_stats_tracking = false;

// Generating Unique Application Key
$application_unique_key = '4fb0ce8557702081d08b32ae0c4e9849';

// trim descriptions, titles in listviews to this value
$listview_max_textlength = 40;

// Maximum time limit for PHP script execution (in seconds)
$php_max_execution_time = 0;

// Set the default timezone as per your preference
$default_timezone = 'UTC';

/* If timezone is configured, try to set it */
if (isset($default_timezone) && function_exists('date_default_timezone_set')) {
    @date_default_timezone_set($default_timezone);
}

//Set the default layout
$default_layout = 'v7';

include_once 'config.security.php';
require_once '/usr/src/vtiger/vtiger-functions.php';
