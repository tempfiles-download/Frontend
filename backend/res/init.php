<?php

function mySQLError() {
    error_log('MySQL connection failed: ' . htmlspecialchars($mysql_connection->error));
    http_response_code(500);
    die("Connection to our database failed.");
}

/**
 * Checks if a file exists.
 * If it doesn't, the connection dies.
 * @since 2.2
 * @param Full path of the file.
 */
function checkFile(string $file) {
    if (file_exists($file)) include_once($file);
    else {
        error_log("Can't find {$file}");
        http_response_code(500);
        die("One or more core files can't be found on the server.");
    }
}

if (file_exists(__DIR__ . '/config.php'))
        $conf = include_once(__DIR__ . '/config.php');
else {
    http_response_code(500);
    die("Can't find config.php.");
}

checkFile(__DIR__ . '/DataStorage.php');
checkFile(__DIR__ . '/Encryption.php');
checkFile(__DIR__ . '/Misc.php');

//MySQL connection.
$mysql_connection = mysqli_connect($conf['mysql-url'], $conf['mysql-user'], $conf['mysql-password'], $conf['mysql-db']) or mySQLError();
