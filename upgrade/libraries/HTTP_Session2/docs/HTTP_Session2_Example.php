<?php
//
// +-----------------------------------------------------------------------+
// | Copyright (c) 2002, Alexander Radivanovich                            |
// | All rights reserved.                                                  |
// |                                                                       |
// | Redistribution and use in source and binary forms, with or without    |
// | modification, are permitted provided that the following conditions    |
// | are met:                                                              |
// |                                                                       |
// | o Redistributions of source code must retain the above copyright      |
// |   notice, this list of conditions and the following disclaimer.       |
// | o Redistributions in binary form must reproduce the above copyright   |
// |   notice, this list of conditions and the following disclaimer in the |
// |   documentation and/or other materials provided with the distribution.|
// | o The names of the authors may not be used to endorse or promote      |
// |   products derived from this software without specific prior written  |
// |   permission.                                                         |
// |                                                                       |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT  |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT      |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT   |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.  |
// |                                                                       |
// +-----------------------------------------------------------------------+
// | Author: Alexander Radivanovich <info@wwwlab.net>                      |
// +-----------------------------------------------------------------------+
//

//ob_start(); //-- For easy debugging --//

require_once 'PEAR.php';
require_once 'HTTP/Session2.php';

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
//PEAR::setErrorHandling(PEAR_ERROR_DIE);

/*
HTTP_Session2::setContainer(
    'DB',
    array(
        'dsn' => 'mysql://root@localhost/database',
        'table' => 'sessiondata'
    )
);
*/
HTTP_Session2::useCookies(true);
HTTP_Session2::start('SessionID', uniqid('MyID'));

?>
<html>
<head>
<style>
body, td {
    font-family: Verdana, Arial, sans-serif;
    font-size: 11px;
}
A:link { color:#003399; text-decoration: none; }
A:visited { color:#6699CC; text-decoration: none; }
A:hover { text-decoration: underline; }
</style>
<title>HTTP Session</title>
</head>
<body style="margin: 5px;">
<?php

/*
if (!isset($variable)) {
    $variable = 0;
    echo("The variable wasn't previously set<br>\n");
} else {
    $variable++;
    echo("Yes, it was set already<br>\n");
}
*/

switch (@$_GET['action']) {
    case 'setvariable':
        HTTP_Session2::set('variable', 'Test string');
        //HTTP_Session2::register('variable');
        break;
    case 'unsetvariable':
        HTTP_Session2::set('variable', null);
        //HTTP_Session2::unregister('variable');
        break;
    case 'clearsession':
        HTTP_Session2::clear();
        break;
    case 'destroysession':
        HTTP_Session2::destroy();
        break;
}

HTTP_Session2::setExpire(60);
HTTP_Session2::setIdle(5);

//echo("session_is_registered('variable'): <b>'" . (session_is_registered('variable') ? "<span style='color: red;'>yes</span>" : "no") . "'</b><br>\n");
//echo("isset(\$GLOBALS['variable']): <b>'" . (isset($GLOBALS['variable']) ? "<span style='color: red;'>yes</span>" : "no") . "'</b><br>\n");

echo("------------------------------------------------------------------<br>\n");
echo("Session name: <b>'" . HTTP_Session2::name() . "'</b><br>\n");
echo("Session id: <b>'" . HTTP_Session2::id() . "'</b><br>\n");
echo("Is new session: <b>'" . (HTTP_Session2::isNew() ? "<span style='color: red;'>yes</span>" : "no") . "'</b><br>\n");
echo("Is expired: <b>'" . (HTTP_Session2::isExpired() ? "<span style='color: red;'>yes</span>" : "no") . "'</b><br>\n");
echo("Is idle: <b>'" . (HTTP_Session2::isIdle() ? "<span style='color: red;'>yes</span>" : "no") . "'</b><br>\n");
//echo("Variable: <b>'" . HTTP_Session2::get('variable') . "'</b><br>\n");
echo("Session valid thru: <b>'" . (HTTP_Session2::sessionValidThru() - time()) . "'</b><br>\n");
echo("------------------------------------------------------------------<br>\n");

if (HTTP_Session2::isNew()) {
    //HTTP_Session2::set('var', 'value');
    //HTTP_Session2::setLocal('localvar', 'localvalue');
    //blah blah blah
}

?>
<div style="background-color: #F0F0F0; padding: 15px; margin: 5px;">
<pre>
$_SESSION:
<?php
var_dump($_SESSION);
?>
</pre>
</div>
<?php

HTTP_Session2::updateIdle();

?>
<p><a href="<?php echo $_SERVER['SCRIPT_NAME'] ?>?action=setvariable">Set variable</a></p>
<p><a href="<?php echo $_SERVER['SCRIPT_NAME'] ?>?action=unsetvariable">Unset variable</a></p>
<p><a href="<?php echo $_SERVER['SCRIPT_NAME'] ?>?action=destroysession">Destroy session</a></p>
<p><a href="<?php echo $_SERVER['SCRIPT_NAME'] ?>?action=clearsession">Clear session data</a></p>
<p><a href="<?php echo $_SERVER['SCRIPT_NAME'] ?>">Reload page</a></p>
</body>
</html>
