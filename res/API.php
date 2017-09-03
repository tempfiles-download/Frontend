<?php

class Encryption {

  public static function decrypt($data, $password, $iv) {
    global $conf;
    return openssl_decrypt($data, $conf['Encryption-Method'], $password, OPENSSL_RAW_DATA, $iv);
  }

  /**
   * Encrypts and encodes the metadata (details) of a file.
   * @global array $conf Configuration variables.
   * @param string $filename 
   * @param string $filesize
   * @param string $filetype
   * @param string $password
   * @param string $iv
   * @return string Returns encoded and encrypted file metadata.
   */
  public static function encryptFileDetails($filename, $filesize, $filetype, $password, $iv) {
    global $conf;
    $filedata = base64_encode($filename) . " " . base64_encode($filesize) . " " . base64_encode($filetype);
    $enc_data = openssl_encrypt($data, $conf['Encryption-Method'], $password, OPENSSL_RAW_DATA, $iv[0]);
    return base64_encode($enc_data);
  }

  /**
   * Encrypts and encodes the content (data) of a file.
   * @global array $conf Configuration variables.
   * @param string $content
   * @param string $password
   * @param string $iv
   * @return string Returns encoded and encrypted file content.
   */
  public static function encryptFileContent($content, $password, $iv) {
    global $conf;
    $enc_content = openssl_encrypt($data, $conf['Encryption-Method'], $password, OPENSSL_RAW_DATA, $iv[1]);
    return base64_encode($enc_content);
  }

  /**
   * Create an IV (Initialization Vector) string.
   * 
   * IV contains of random data from a "random" source. In this case the source is openssl.
   * 
   * @return type Returns an IV string encoded with base64.
   */
  public static function getIV() {
    return mb_strcut(base64_encode(openssl_random_pseudo_bytes(16)), 0, 16);
  }

}

class data_storage {
  /**
   * data_storage handels all MySQL database connecting functions.
   */

  /**
   * Set the current views and max views for a specified file.
   * When the current views are equal or exceeds max views the file will be deleted and the user will get a 404 error.
   * @global array $conf Configuration variables.
   * @global object $mysql_connection MySQL connetion.
   * @param string $maxViews The new maximum amount of views to set on a file.
   * @param string $newViews The new current views to set on a file.
   * @param string $id The ID for the file.
   * @return boolean Returns true if the change was successful. Returns false otherwise.
   */
  public static function setViews($maxViews, $newViews, $id) {
    global $conf;
    global $mysql_connection;
    $views = base64_encode($newViews . "," . $maxViews);
    $query = $mysql_connection->prepare("UPDATE `" . $conf['mysql-table'] . "` SET `maxviews` = ? WHERE `id` = ?");
    $bp = $query->bind_param("ss", $views, $id);
    if (false === $bp) {
      error_log('bind_param() failed: ' . htmlspecialchars($query->error));
      return false;
    }
    return $query->execute();
  }

  /**
   * Delete a specific file.
   * @global array $conf Configuration variables.
   * @global object $mysql_connection MySQL connetion.
   * @param string $id The ID for the file.
   * @return boolean Returns true if the deletion was sucessful. Returns false otherwise.
   */
  public static function deleteFile($id) {
    global $conf;
    global $mysql_connection;
    $query = $mysql_connection->prepare("DELETE FROM `" . $conf['mysql-table'] . "` WHERE `id` = ?");
    $query->bind_param("s", $id);
    return $query->execute();
  }

  /**
   * Get the metadata and content of a file.
   * @global array $conf Configuration variables.
   * @global object $mysql_connection MySQL connetion.
   * @param string $id The ID for the file.
   * @param string $password
   * @return mixed Returns array of filedata, filecontent & maxviews if fetching of the specified file was sucessful. Returns NULL otherwise.
   */
  public static function getFile($id, $password) {
    global $conf;
    global $mysql_connection;
    $query = $mysql_connection->prepare("SELECT `metadata`, `content`, `maxviews` FROM `" . $conf['mysql-table'] . "` WHERE `id` = ?");
    $query->bind_param("s", $id);
    $query->execute();
    $query->store_result();
    $query->bind_result($enc_filedata, $enc_content, $enc_maxviews);
    $query->fetch();
    if ($enc_maxviews != NULL) {
      $maxviews = explode(",", base64_decode($enc_maxviews));
    } else {
      $maxviews = $enc_maxviews;
    }
    if ($enc_content != NULL) {
      $filedata = base64_decode($enc_filedata);
      $filecontent = base64_decode($enc_content);
      error_log($enc_filedata);
      return [$filedata, $filecontent, $maxviews];
    } else {
      return NULL;
    }
  }

  /**
   * Upload a file to the database.
   * @global array $conf Configuration variables.
   * @global object $mysql_connection MySQL connetion.
   * @param string $enc_content Encoded and encrypted file content.
   * @param stromg $enc_filedata Encoded and encrypted file data.
   * @param int $maxviews The max amount of views for a file.
   * @return string Returns the ID of the uploaded file if the upload was sucessful. Returns 0 otherwise.
   */
  public static function uploadFile($enc_content, $enc_filedata, $maxviews) {
    global $conf;
    global $mysql_connection;
    $id = strtoupper(uniqid("d"));
    $NULL = NULL;
    if ($maxviews != NULL) {
      $enc_maxviews = base64_encode('[0,' . $maxviews . ']');
    } else {
      $enc_maxviews = NULL;
    }

    $query = $mysql_connection->prepare("INSERT INTO `" . $conf['mysql-table'] . "` (id, metadata, content, maxviews) VALUES (?, ?, ?, ?)");
    $query->bind_param("ssbs", $id, $enc_filedata, $NULL, $enc_maxviews);
    $query->send_long_data(2, $enc_content);
    $query->execute();
    $query->close();
    return $id;
  }

  /**
   * 
   * @param string $file
   * @param string $metadata
   * @param string $maxviews
   * @param string $password
   * @return boolean
   */
  public static function getID($file, $metadata, $maxviews = NULL, $password) {
    $iv = array(Encryption::getIV(), Encryption::getIV());
    $enc_filecontent = Encryption::encryptFileContent($file, $password, $iv[0]);
    $enc_filedata = Encryption::encryptFileDetails($file['name'], $file['size'], $file['type'], $password, $iv[1]);
    $id = Encryption::uploadPlainFile($enc_filecontent, $enc_filedata, $maxviews);
    if ($id != FALSE) {
      return array(true, $id);
    } else {
      return array(false, "Connection to our database failed.");
    }
  }

}

class Misc {

  public static function getVar($name) {
    if (filter_input(INPUT_GET, $name) != NULL)
      return filter_input(INPUT_GET, $name);
    if (filter_input(INPUT_POST, $name) != NULL)
      return filter_input(INPUT_POST, $name);
  }

  public static function generatePassword() {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $length = rand(3, 10);
    return substr(str_shuffle($chars), 0, $length);
  }

  /**
   * Convert metric data prefixes to bytes.
   * @author John V.
   * @link http://stackoverflow.com/a/11807179
   * @param string $from String to convert.
   * @return int Output of $from in bytes.
   */
  public static function convertToBytes($from) {
    $number = substr($from, 0, -2);
    switch (strtoupper(substr($from, -2))) {
      case "KB":
        return $number * 1024;
      case "MB":
        return $number * pow(1024, 2);
      case "GB":
        return $number * pow(1024, 3);
      case "TB":
        return $number * pow(1024, 4);
      case "PB":
        return $number * pow(1024, 5);
      default:
        return $from;
    }
  }

}
