<?php

class Encryption {

  public static function decrypt($data, $password, $iv) {
    include __DIR__ . '/config.php';
    return openssl_decrypt($data, $conf['Encryption-Method'], $password, OPENSSL_RAW_DATA, $iv);
  }

  public static function encrypt($data, $password, $iv) {
    include __DIR__ . '/config.php';
    return openssl_encrypt($data, $conf['Encryption-Method'], $password, OPENSSL_RAW_DATA, $iv);
  }

}

class data_storage {

  public static function getFile($id, $password) {
    include __DIR__ . '/config.php';
    $con = mysqli_connect($conf['mysql-url'], $conf['mysql-user'], $conf['mysql-password'], $conf['mysql-db']) or die("Connection problem.");
    $query = $con->prepare("SELECT `iv`, `metadata`, `content` FROM `" . $conf['mysql-table'] . "` WHERE `id` = ?");
    $query->bind_param("s", $id);
    $query->execute();
    $query->bind_result($iv_encoded, $enc_filedata, $enc_content);
    $query->fetch();
    $iv = explode(",", base64_decode($iv_encoded));
    $filedata = Encryption::decrypt(base64_decode($enc_filedata), $password, $iv[0]);
    $filecontent = Encryption::decrypt(base64_decode($enc_content), $password, $iv[1]);
    return [
        $filedata, $filecontent
    ];
  }

  public static function uploadFile($content, $filename, $filesize, $filetype, $password) {
    include __DIR__ . '/config.php';
    $id = strtoupper(uniqid("d"));
    $iv = array(mb_strcut(base64_encode(openssl_random_pseudo_bytes(16)), 0, 16), mb_strcut(base64_encode(openssl_random_pseudo_bytes(16)), 0, 16));
    $enc_filedata = base64_encode(Encryption::encrypt(implode(" ", array($filename, $filesize, $filetype)), $password, $iv[0]));
    $enc_content = base64_encode(Encryption::encrypt($content, $password, $iv[1]));
    $exportable_iv = base64_encode(implode(",", $iv));
    $NULL = NULL;

    $con = mysqli_connect($conf['mysql-url'], $conf['mysql-user'], $conf['mysql-password'], $conf['mysql-db']) or die("Connection problem.");
    $query = $con->prepare("INSERT INTO `" . $conf['mysql-table'] . "` (id, iv, metadata, content) VALUES (?, ?, ?, ?)");
    if (false === $query) {
      error_log('prepare() failed: ' . htmlspecialchars($con->error));
      return false;
    }

    $bp = $query->bind_param("sssb", $id, $exportable_iv, $enc_filedata, $NULL);
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

  public static function getID($file, $password) {
    if ($file != NULL) {
      if ($password != NULL) {
        $fileContent = file_get_contents($file['tmp_name']);
        $id = data_storage::uploadFile($fileContent, $file["name"], $file["size"], $file["type"], $password);
        if ($id != false) {
          return array(true, $id);
        } else {
          return array(false, "Connection to our database failed.");
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

}
