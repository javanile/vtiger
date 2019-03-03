<?php

require_once __DIR__.'/autoload.php';

echo "[vtiger] starting up...\n";
if (!$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT)) {
    echo '[vtiger] database: '.mysqli_connect_errno().' - '.mysqli_connect_error()."\n";
    exit(1);
}

echo "[vtiger] update adminstrator settings\n";
$password = 'adpexzg3FUZAk';
mysqli_query($db, "
  UPDATE vtiger_users 
     SET user_password = '{$password}'
       , crypt_type = '' 
   WHERE id = '1'
");
