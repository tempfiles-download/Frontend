<?php

function mySQLError() {
  error_log('prepare() failed: ' . htmlspecialchars($con->error));
  die("Connection to our database failed.");
}

if (file_exists(__DIR__ . '/config.php')) {

  $conf = include(__DIR__ . '/config.php');
} else {
  die;
}

if (file_exists(__DIR__ . '/API.php')) {
  include(__DIR__ . '/API.php');
}

$mysql_connection = mysqli_connect($conf['mysql-url'], $conf['mysql-user'], $conf['mysql-password'], $conf['mysql-db']) or mySQLError();
