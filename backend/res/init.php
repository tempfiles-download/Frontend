<?php

function mySQLError() {
    error_log('prepare() failed: ' . htmlspecialchars($mysql_connection->error));
    die("Connection to our database failed.");
}

if (file_exists(__DIR__ . '/config.php')) {
    $conf = include(__DIR__ . '/config.php');
} else {
    die;
}

if (file_exists(__DIR__ . '/DataStorage.php')) {
    include(__DIR__ . '/DataStorage.php');
}
if (file_exists(__DIR__ . '/Encryption.php')) {
    include(__DIR__ . '/Encryption.php');
}
if (file_exists(__DIR__ . '/Misc.php')) {
    include(__DIR__ . '/Misc.php');
}

//MySQL connection.
$mysql_connection = mysqli_connect($conf['mysql-url'], $conf['mysql-user'], $conf['mysql-password'], $conf['mysql-db']) or mySQLError();
