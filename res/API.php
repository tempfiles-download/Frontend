<?php

class Encryption {

  public static function decrypt($data, $password, $iv) {
    global $conf;
    return openssl_decrypt($data, $conf['Encryption-Method'], $password, OPENSSL_RAW_DATA, $iv);
  }

  public static function encrypt($data, $password, $iv) {
    global $conf;
    return openssl_encrypt($data, $conf['Encryption-Method'], $password, OPENSSL_RAW_DATA, $iv);
  }

}

class data_storage {

  public static function setViews($maxViews, $newViews, $id) {
    global $conf;
    global $con;
    $views = base64_encode($newViews . "," . $maxViews);
    $query = $con->prepare("UPDATE `" . $conf['mysql-table'] . "` SET `maxviews` = ? WHERE `id` = ?");
    $bp = $query->bind_param("ss", $views, $id);
    if (false === $bp) {
      error_log('bind_param() failed: ' . htmlspecialchars($query->error));
      return false;
    }
    return $query->execute();
  }

  public static function deleteFile($id) {
    global $conf;
    global $con;
    $query = $con->prepare("DELETE FROM `" . $conf['mysql-table'] . "` WHERE `id` = ?");
    $query->bind_param("s", $id);
    return $query->execute();
  }

  public static function getFile($id, $password) {
    global $conf;
    global $con;
    $query = $con->prepare("SELECT `iv`, `metadata`, `content`, `maxviews` FROM `" . $conf['mysql-table'] . "` WHERE `id` = ?");
    $query->bind_param("s", $id);
    $query->execute();
    $query->store_result();
    $query->bind_result($iv_encoded, $enc_filedata, $enc_content, $enc_maxviews);
    $query->fetch();
    $iv = explode(",", base64_decode($iv_encoded));
    if ($enc_maxviews != NULL) {
      $maxviews = explode(",", base64_decode($enc_maxviews));
    } else {
      $maxviews = $enc_maxviews;
    }
    if ($enc_content != NULL) {
      $filedata = Encryption::decrypt(base64_decode($enc_filedata), $password, $iv[0]);
      $filecontent = Encryption::decrypt(base64_decode($enc_content), $password, $iv[1]);
      return [
          $filedata, $filecontent, $maxviews
      ];
    } else {
      return NULL;
    }
  }

  public static function uploadFile($content, $filename, $filesize, $filetype, $password, $maxviews) {
    global $conf;
    global $con;
    $id = strtoupper(uniqid("d"));
    $iv = array(mb_strcut(base64_encode(openssl_random_pseudo_bytes(16)), 0, 16), mb_strcut(base64_encode(openssl_random_pseudo_bytes(16)), 0, 16));
    $enc_filedata = base64_encode(Encryption::encrypt(implode(" ", array($filename, $filesize, $filetype)), $password, $iv[0]));
    $enc_content = base64_encode(Encryption::encrypt($content, $password, $iv[1]));
    $exportable_iv = base64_encode(implode(",", $iv));
    $NULL = NULL;
    if ($maxviews != NULL) {
      $enc_maxviews = base64_encode('[0,' . $maxviews . ']');
    } else {
      $enc_maxviews = NULL;
    }

    $query = $con->prepare("INSERT INTO `" . $conf['mysql-table'] . "` (id, iv, metadata, content, maxviews) VALUES (?, ?, ?, ?, ?)");
    if (false === $query) {
      error_log('prepare() failed: ' . htmlspecialchars($con->error));
      return false;
    }

    $bp = $query->bind_param("sssbs", $id, $exportable_iv, $enc_filedata, $NULL, $enc_maxviews);
    $query->send_long_data(3, $enc_content);
    if (false === $bp) {
      error_log('bind_param() failed: ' . htmlspecialchars($query->error));
      return false;
    }
    $bp = $query->execute();
    if (false === $bp) {
      error_log('execute() failed: ' . htmlspecialchars($query->error));
      return false;
    }
    $query->close();
    return $id;
  }

  public static function getID($file, $password, $maxviews = NULL) {
    if ($file != NULL) {
      if ($password != NULL) {
        if (is_numeric($maxviews) || $maxviews == NULL) {
          $fileContent = file_get_contents($file['tmp_name']);
          $id = data_storage::uploadFile($fileContent, $file["name"], $file["size"], $file["type"], $password, $maxviews);
          if ($id != false) {
            return array(true, $id);
          } else {
            return array(false, "Connection to our database failed.");
          }
        } else {
          return array(false, "'maxviews' is not a number.");
        }
      } else {
        return array(false, "Password not set.");
      }
    } else {
      return array(false, "File not found.");
    }
  }

  public static function testDownload($id, $password) {
    $d = data_storage::getFile($id, $password);
    if ($d[0] != NULL) {
      return true;
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

}
