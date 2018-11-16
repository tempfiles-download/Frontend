<?php

class DataStorage {
    /**
     * data_storage handels all MySQL database connecting functions.
     */

    /**
     * Set the current views and max views for a specified file.
     * When the current views are equal or exceeds max views the file will be deleted and the user will get a 404 error.
     * @global array $conf Configuration variables.
     * @global object $mysql_connection MySQL connection.
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
        $result = $query->execute();
        $query->close();
        return $result;
    }

    /**
    * Delete all files older than 1 day.
    * @return boolean Returns true if the query was sucessful, otherwise false.
    */
    public static function deleteOldFiles(){
      global $conf;
      global $mysql_connection;
      $query = $mysql_connection->prepare("DELETE FROM `files` WHERE `time` < DATE_SUB(NOW(), INTERVAL 1 DAY)");
      $result = $query->execute();
      $query->close();
      return($result);
    }

    /**
     * Get the metadata and content of a file.
     * @global array $conf Configuration variables.
     * @global object $mysql_connection MySQL connetion.
     * @param string $id The ID for the file.
     * @param string $password Description
     * @return mixed Returns array of filedata, filecontent & maxviews if fetching of the specified file was sucessful. Returns NULL otherwise.
     */
    public static function getFile($id, $password) {
        global $conf;
        global $mysql_connection;
        $query = $mysql_connection->prepare("SELECT `iv`, `metadata`, `content`, `maxviews` FROM `" . $conf['mysql-table'] . "` WHERE `id` = ?");
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
            $filedata = Encryption::decrypt(base64_decode($enc_filedata), $password, $iv[1]);
            $filecontent = Encryption::decrypt(base64_decode($enc_content), $password, $iv[0]);
            return [$filedata, $filecontent, $maxviews];
        } else {
            return NULL;
        }
    }

    /**
     * Upload a file to the database.
     * @global array $conf Configuration variables.
     * @global object $mysql_connection MySQL connection.
     * @param string $enc_content Encoded and encrypted file content.
     * @param string $enc_filedata Encoded and encrypted file data.
     * @param int $maxviews The max amount of views for a file.
     * @return string Returns the ID of the uploaded file if the upload was sucessful. Returns 0 otherwise.
     */
    public static function uploadFile($enc_content, $iv, $enc_filedata, $maxviews) {
        global $conf;
        global $mysql_connection;
        $id = strtoupper(uniqid("d"));
        $NULL = NULL;
        if ($maxviews != NULL) {
            $enc_maxviews = base64_encode('[0,' . $maxviews . ']');
        } else {
            $enc_maxviews = NULL;
        }
        $query = $mysql_connection->prepare("INSERT INTO `" . $conf['mysql-table'] . "` (id, iv, metadata, content, maxviews) VALUES (?, ?, ?, ?, ?)");
        if (false === $query) {
            error_log('prepare() failed: ' . htmlspecialchars($mysql_connection->error));
            return false;
        }
        $bp = $query->bind_param("sssbs", $id, $iv, $enc_filedata, $NULL, $enc_maxviews);
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

    /**
     * @param string $file
     * @param string $password
     * @param string $maxviews
     * @param string $deletionpass
     * @return boolean
     */
    public static function getID($file, $password, $maxviews = NULL, $deletionpass = NULL) {
        global $conf;
        if ($file != NULL) {
            $maxsize = Misc::convertToBytes($conf['max-file-size']);
            if ($file['size'] <= $maxsize) {
                if ($password != NULL) {
                    if (is_numeric($maxviews) || $maxviews == NULL) {
                        $iv = array(Encryption::getIV(), Encryption::getIV());
                        $fileContent = file_get_contents($file['tmp_name']);
                        $enc_filecontent = Encryption::encryptFileContent($fileContent, $password, $iv[0]);
                        $enc_filedata = Encryption::encryptFileDetails($file, $deletionpass, $password, $iv[1]);
                        $enc_iv = base64_encode($iv[0] . "," . $iv[1]);
                        $id = DataStorage::uploadFile($enc_filecontent, $enc_iv, $enc_filedata, $maxviews);
                        if ($id != FALSE) {
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
                return array(false, "File size too large. Maximum allowed " . $conf['max-file-size'] . " (currently " . $file['size'] . ")");
            }
        } else {
            return array(false, "File not found.");
        }
    }

}
