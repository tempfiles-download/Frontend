<?php

// Load resources.

$conf = checkFile(__DIR__ . '/config.php');
checkFile(__DIR__ . '/DataStorage.php');
checkFile(__DIR__ . '/Encryption.php');
checkFile(__DIR__ . '/Misc.php');
checkFile(__DIR__ . '/File.php');
checkFile(__DIR__ . '/API.php');

// Load MySQL connection unless the script is run by PHPUnit.
if (!isset($_ENV['phpunit']))
    $mysql_connection = mysqli_connect($conf['mysql-url'], $conf['mysql-user'], $conf['mysql-password'], $conf['mysql-db'])
    or mySQLError($mysql_connection);


/**
 * Outputs MySQL error.
 *
 * @param $mysql_connection The failed MySQL connection.
 */
function mySQLError($mysql_connection) {
    error_log('MySQL connection failed: ' . htmlspecialchars($mysql_connection->error));
    http_response_code(500);
    die("Connection to our database failed.");
}

/**
 * Checks if a file exists.
 * If it doesn't, the connection dies.
 *
 * @param string $file Full path of the file.
 * @return object|null Returns the file if found.
 * @since 2.2
 */
function checkFile(string $file) {
    if (file_exists($file))
        return require_once($file);
    else {
        error_log("Can't find {$file}");
        http_response_code(500);
        die("One or more core files can't be found on the server.");
    }
}


