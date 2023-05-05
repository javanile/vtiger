<?php

require_once __DIR__.'/vtiger-autoload.php';

function encrypt_password($username, $user_password, $crypt_type = '')
{
    $salt = substr($username, 0, 2);
    if ($crypt_type == '') {
        $crypt_type = 'MD5';
    }
    if ($crypt_type == 'MD5') {
        $salt = '$1$'.$salt.'$';
    } elseif ($crypt_type == 'BLOWFISH') {
        $salt = '$2$'.$salt.'$';
    } elseif ($crypt_type == 'PHP5.3MD5') {
        $salt = '$1$'.str_pad($salt, 9, '0');
    }
    $encrypted_password = crypt($user_password, $salt);

    return $encrypted_password;
}

echo "[vtiger] Looking for database...\n";
if (!$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT)) {
    echo '[vtiger] Database: '.mysqli_connect_errno().' - '.mysqli_connect_error()."\n";
    //exit(1);
}

echo "[vtiger] Update administrator settings\n";
if (!file_exists($lock = __DIR__.'/startup.lock')) {
    // update password
    /*
    $username = getenv('VT_ADMIN_USER') ?: 'admin';
    $password = encrypt_password($username, getenv('VT_ADMIN_PASSWORD') ?: 'admin');
    mysqli_query($db, "
        UPDATE vtiger_users
        SET user_name = '{$username}'
          , user_password = '{$password}'
          , crypt_type = ''
        WHERE id = '1'
    ");
    file_put_contents($lock, json_encode(['lock' => true]));
    */
}
