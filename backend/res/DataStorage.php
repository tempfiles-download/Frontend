<?php

class DataStorage {
    /**
     * data_storage handles all MySQL database connecting functions.
     */

    /**
     * Set the current views and max views for a specified file.
     * When the current views are equal or exceeds max views the file will be deleted and the user will get a 404 error.
     * @global array $conf Configuration variables.
     * @global object $mysql_connection MySQL connection.
     * @param string $maxViews The new maximum amount of views to set on a file.
     * @param string $newViews The new current views to set on a file.
     * @param object $id The ID for the file.
     * @return boolean Returns true if the change was successful. Returns false otherwise.
     */
    public static function setViews(string $maxViews, string $newViews, string $id) {
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
     * @param object $id The ID for the file.
     * @return boolean Returns true if the deletion was sucessful. Returns false otherwise.
     */
    public static function deleteFile(string $id) {
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
    public static function deleteOldFiles() {
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
     * @param object $id The ID for the file.
     * @param string $password Description
     * @return mixed Returns array of filedata, filecontent & maxviews if fetching of the specified file was sucessful. Returns NULL otherwise.
     */
    public static function getFile(string $id, string $password) {
        global $conf;
        global $mysql_connection;

        $query = $mysql_connection->prepare("SELECT `iv`, `metadata`, `content`, `maxviews` FROM `" . $conf['mysql-table'] . "` WHERE `id` = ?");
        $query->bind_param("s", $id);
        $query->execute();
        $query->store_result();
        $query->bind_result($iv_encoded, $enc_filedata, $enc_content, $enc_maxviews);
        $query->fetch();

        $iv = explode(",", base64_decode($iv_encoded));
        require_once __DIR__ . '/File.php';
        $file = new File(NULL, $id);
        if ($enc_maxviews != NULL)

        // $views = [currentViews, maxViews];
                $views = explode(",", base64_decode($enc_maxviews));
        if (isset($views)) {
            $file->setCurrentViews($views[0]);
            $file->setMaxViews($views[1]);
        }

        if ($enc_content != NULL) {

            $metadata_string = Encryption::decrypt(base64_decode($enc_filedata), $password, $iv[1]);
            $metadata_array = explode(' ', $metadata_string);
            $metadata = ['name' => $metadata_array[0], 'size' => $metadata_array[1], 'type' => $metadata_array[2]];
            $file->setMetaData($metadata);
            $file->setContent(Encryption::decrypt(base64_decode($enc_content), $password, $iv[0]));
            return $file;
        }

        return NULL;
    }

    /**
     * Upload a file to the database.
     * @global array $conf Configuration variables.
     * @global object $mysql_connection MySQL connection.
     * @param object $file File to upload.
     * @param string $password Password to encrypt the file content and metadata with.
     * @return boolean Returns the ID of the uploaded file if the upload was sucessful. Returns 0 otherwise.
     */
    public static function uploadFile(File $file, string $password) {
        global $conf;
        global $mysql_connection;
        $iv = ['content' => Encryption::getIV(), 'metadata' => Encryption::getIV()];
        $maxviews = $file->getMaxViews();
        $enc_filecontent = Encryption::encryptFileContent($file->getContent(), $password, $iv['content']);
        $enc_filemetadata = Encryption::encryptFileDetails($file->getMetaData(), $file->getDeletionPassword(), $password, $iv['metadata']);
        $enc_iv = base64_encode($iv['content'] . "," . $iv['metadata']);

        if ($maxviews != NULL) {
            $enc_maxviews = base64_encode('[0,' . $maxviews . ']');
        } else {
            $enc_maxviews = NULL;
        }

        try {
            $query = $mysql_connection->prepare("INSERT INTO `" . $conf['mysql-table'] . "` (id, iv, metadata, content, maxviews) VALUES (?, ?, ?, ?, ?)");
            if (false === $query) {
                throw new Exception('prepare() failed: ' . htmlspecialchars($mysql_connection->error));
            }
            $id = $file->getID();
            $bp = $query->bind_param("sssbs", $id, $enc_iv, $enc_filemetadata, $NULL, $enc_maxviews);

            //send content blob to query
            $query->send_long_data(3, $enc_filecontent);
            if (false === $bp) {
                throw new Exception('bind_param() failed: ' . htmlspecialchars($query->error));
            }

            $bp = $query->execute();
            if (false === $bp) {
                throw new Exception('execute() failed: ' . htmlspecialchars($query->error));
            }

            $query->close();
            return true;
        } catch (Exception $e) {
            error_log($e);
            return false;
        }

        $query->close();
    }

    /**
     * @param string $file
     * @param string $password
     * @param string $maxviews
     * @param string $deletionpass
     * @return boolean
     */
    public static function getID(array $fileContent, string $password, File $file) {
        global $conf;
        $metadata = ['size' => $fileContent['size'], 'name' => $fileContent['name'], 'type' => $fileContent['type']];
        $file->setMetaData($metadata);
        try {
            if ($file->getMetaData('size') <= Misc::convertToBytes($conf['max-file-size'])) {
                if ($password != NULL) {
                    $file->setContent(file_get_contents($fileContent['tmp_name']));
                    if ($upload = DataStorage::uploadFile($file, $password)) {
                        return true;
                    } else {
                        //throw new Exception("Connection to our database failed.");
                    }
                } else {
                    throw new Exception("Password not set.");
                }
            } else {
                throw new Exception("File size too large. Maximum allowed " . $conf['max-file-size'] . " (currently " . $file->getMetaData('size') . ")");
            }
        } catch (Exception $e) {
            error_log($e);
            return($e->getMessage());
        }
    }

}
