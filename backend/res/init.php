<?php

function mySQLError() {
    error_log('prepare() failed: ' . htmlspecialchars($mysql_connection->error));
    die("Connection to our database failed.");
}

if (file_exists(__DIR__ . '/config.php')) {
    $conf = include_once(__DIR__ . '/config.php');
} else {
    die;
}

if (file_exists(__DIR__ . '/DataStorage.php')) {
    include_once(__DIR__ . '/DataStorage.php');
}
if (file_exists(__DIR__ . '/Encryption.php')) {
    include_once(__DIR__ . '/Encryption.php');
}
if (file_exists(__DIR__ . '/Misc.php')) {
    include_once(__DIR__ . '/Misc.php');
}

//MySQL connection.
$mysql_connection = mysqli_connect($conf['mysql-url'], $conf['mysql-user'], $conf['mysql-password'], $conf['mysql-db']) or mySQLError();
