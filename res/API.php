<?php

class Login {

    public static function register($username, $password, $yubikey) {
        if (Login::userExists($username) == false) {
            $hash = password_hash($password, PASSWORD_BCRYPT, Login::generateHashCost());
            include __DIR__ . '/config.php';
            $con = mysqli_connect($conf['mysql-url'], $conf['mysql-user'], $conf['mysql-password'], $conf['mysql-db']) or die("Connection problem.");
            $query = $con->prepare("INSERT INTO `" . $conf['login-table'] . "` (`username`, `password`, `yubikey`) VALUES (?, ?, ?);");
            $query->bind_param("sss", $username, $hash, $yubikey);
            $query->execute();
            return 1;
        }
        return 0;
    }

    public static function getPassword($username, $password) {
        include __DIR__ . '/config.php';
        $con = mysqli_connect($conf['mysql-url'], $conf['mysql-user'], $conf['mysql-password'], $conf['mysql-db']) or die("Connection problem.");
        $query = $con->prepare("SELECT * FROM `" . $conf['login-table'] . "` WHERE username = ?");
        $query->bind_param("s", $username);
        $query->execute();
        $query->bind_result($dbuser, $dbpassword, $dbyubikey);
        if ($query->fetch()) {
            if (password_verify($password, $dbpassword)) {
                return true;
            }
        }
        return false;
    }

    public static function verifyYubikey($username, $otp) {
        include __DIR__ . '/config.php';
        $con = mysqli_connect($conf['mysql-url'], $conf['mysql-user'], $conf['mysql-password'], $conf['mysql-db']) or die("Connection problem.");
        $query = $con->prepare("SELECT `yubikey` FROM `" . $conf['login-table'] . "` WHERE username = ?");
        $query->bind_param("s", $username);
        $query->execute();
        $query->bind_result($dbyubikey);
        if ($query->fetch()) {
            if (substr($otp, 0, 12) == $dbyubikey) {
                return true;
            }
        }
        return false;
    }

    public static function doLogin($username, $password) {
        if (Login::userExists($username)) {
            if (Login::getPassword($username, $password)) {
                return true;
            }
        }
        return false;
    }

    public static function userExists($username) {
        include __DIR__ . '/config.php';
        $con = mysqli_connect($conf['mysql-url'], $conf['mysql-user'], $conf['mysql-password'], $conf['mysql-db']) or die("Connection problem.");
        $query = $con->prepare("SELECT COUNT(*) AS num FROM `" . $conf['login-table'] . "` WHERE `username` = ?");
        $query->bind_param("s", $username);
        $query->execute();
        $result = $query->get_result();
        while ($row = $result->fetch_array(MYSQLI_NUM)) {
            foreach ($row as $r) {
                if ($r > 0) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function generateHashCost() {
        $timeTarget = 0.05;
        $cost = 8;
        do {
            $cost++;
            $start = microtime(true);
            password_hash("test", PASSWORD_BCRYPT, ["cost" => $cost]);
            $end = microtime(true);
        } while (($end - $start) < $timeTarget);
        return $cost;
    }

    public static function updatePassword($username, $oldpassword, $password) {
        if (Login::getPassword($username, $oldpassword)) {
            $hash = password_hash($password, PASSWORD_BCRYPT, Login::generateHashCost($password));
            include_once __DIR__ . '/config.php';
            $con = mysqli_connect($conf['mysql-url'], $conf['mysql-user'], $conf['mysql-password'], $conf['mysql-db']) or die("Connection problem.");
            $query = $con->prepare("UPDATE `" . $conf['login-table'] . "` SET `password`=? WHERE `username`=?;");
            $query->bind_param("ss", $hash, $username);
            $query->execute();
            return true;
        }
        return false;
    }

}

class Encryption {

    public static function decrypt($data, $password, $iv) {
        include __DIR__ . '/config.php';
        return openssl_decrypt($data, $conf['Encryption-Method'], $password, OPENSSL_RAW_DATA, $iv);
    }

    public static function encrypt($data, $password, $iv) {
        include __DIR__ . '/config.php';
        //openssl_encrypt($data, $method, $password, $options, $iv)
        return openssl_encrypt($data, $conf['Encryption-Method'], $password, OPENSSL_RAW_DATA, $iv);
    }

}

class data_storage {

    public static function getFile($id, $password) {
        include __DIR__ . '/config.php';
        $con = mysqli_connect($conf['mysql-url'], $conf['mysql-user'], $conf['mysql-password'], $conf['mysql-db']) or die("Connection problem.");
        $query = $con->prepare("SELECT iv, metadata, content FROM `" . $conf['mysql-table'] . "` WHERE `id` = ?");
        $query->bind_param("s", $id);
        $query->execute();
        $query->bind_result($iv, $enc_filedata, $enc_content);
        $query->fetch();
        $filedata = Encryption::decrypt($enc_filedata, $password, $iv);
        $filecontent = Encryption::decrypt($enc_content, $password, $iv);
        return [
            $filedata, $filecontent
        ];
    }

    public static function uploadFile($content, $filename, $filesize, $filetype, $password) {
        include __DIR__ . '/config.php';
        $id = strtoupper(uniqid("d"));
        $iv = openssl_random_pseudo_bytes(16);
        $enc_filedata = Encryption::encrypt(implode(" ", array($filename, $filesize, $filetype)), $password, $iv);
        $enc_content = Encryption::encrypt($content, $password, $iv);
        $NULL = NULL;
        $con = mysqli_connect($conf['mysql-url'], $conf['mysql-user'], $conf['mysql-password'], $conf['mysql-db']) or die("Connection problem.");
        $query = $con->prepare("INSERT INTO `" . $conf['mysql-table'] . "` (id, iv, metadata, content) VALUES (?, ?, ?, ?)");
        if (false === $query) {
            error_log('prepare() failed: ' . htmlspecialchars($con->error));
        }
        $bp = $query->bind_param("sssb", $id, $iv, $enc_filedata, $NULL);
        $query->send_long_data(3, $enc_content);
        if (false === $bp) {
            error_log('bind_param() failed: ' . htmlspecialchars($query->error));
        }
        $bp = $query->execute();
        if (false === $bp) {
            error_log('execute() failed: ' . htmlspecialchars($query->error));
        }
        $query->close();
        return $id;
    }

    public static function checkUpload() {
        if (isset($_POST['upload-submit'])) {
            $file = $_FILES["uploadedFile"];
            $fileContent = file_get_contents($file['tmp_name']);
            return data_storage::uploadFile($fileContent, $file["name"], $file["size"], $file["type"], $_POST['upload-password']);
        }
    }

    public static function testDownload($id, $password) {
        $d = data_storage::getFile($id, $password);
        if ($d[0] != NULL) {
            return true;
        }
    }

}
