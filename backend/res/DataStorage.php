<?php

/**
 * DataStorage handles all MySQL database connecting functions.
 * @since 2.0
 */
class DataStorage {

    /**
     * Set the current views and max views for a specified file.
     * When the current views are equal or exceeds max views the file will be deleted and the user will get a 404 error.
     * @since 2.0
     * @global array $conf Configuration variables.
     * @global object $mysql_connection MySQL connection.
     * @param string $maxViews The new maximum amount of views to set on a file.
     * @param string $newViews The new current views to set on a file.
     * @param string $id The ID for the file.
     * @return boolean Returns TRUE if the change was successful, otherwise FALSE.
     */
    public static function setViews(string $maxViews, string $newViews, string $id) {
        global $conf;
        global $mysql_connection;
        $views = base64_encode($newViews . "," . $maxViews);
        $query = $mysql_connection->prepare("UPDATE `" . $conf['mysql-table'] . "` SET `maxviews` = ? WHERE `id` = ?");
        if (!$query->bind_param("ss", $views, $id)) {
            error_log('bind_param() failed: ' . htmlspecialchars($query->error));
            return false;
        }
        $result = $query->execute();
        $query->close();
        return $result;
    }

    /**
     * Delete a specific file.
     * @since 2.0
     * @global array $conf Configuration variables.
     * @global object $mysql_connection MySQL connection.
     * @param string $id The ID for the file.
     * @return boolean Returns TRUE if the deletion was successful, otherwise FALSE.
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
     * @since 2.1
     * @return boolean Returns true if the query was successful, otherwise false.
     */
    public static function deleteOldFiles() {
        global $conf;
        global $mysql_connection;
        $query = $mysql_connection->prepare("DELETE FROM `files` WHERE `time` < DATE_SUB(NOW(), INTERVAL 1 DAY)");
        $result = $query->execute();
        $query->close();
        return $result;
    }

    /**
     * Get the metadata and content of a file.
     * @since 2.0
     * @global array $conf Configuration variables.
     * @global object $mysql_connection MySQL connection.
     * @param string $id The ID for the file.
     * @param string $password Description
     * @return mixed Returns the downloaded and decrypted file (object) if successfully downloaded and decrypted, otherwise NULL (boolean).
     */
    public static function getFile(string $id, string $password) {
        global $conf;
        global $mysql_connection;
        require_once __DIR__ . '/File.php';

        $query = $mysql_connection->prepare("SELECT `iv`, `metadata`, `content`, `maxviews` FROM `" . $conf['mysql-table'] . "` WHERE `id` = ?");
        $query->bind_param("s", $id);
        $query->execute();
        $query->store_result();
        $query->bind_result($iv_encoded, $enc_filedata, $enc_content, $enc_maxviews);
        $query->fetch();
        $query->close();

        $file = new File(NULL, $id);
        $iv = explode(",", base64_decode($iv_encoded));

        if ($enc_maxviews !== NULL)
        // NOTE: $views = [currentViews, maxViews];
                $views = explode(",", base64_decode($enc_maxviews));

        if (isset($views)) {
            $file->setCurrentViews((int) $views[0]);
            $file->setMaxViews((int) $views[1]);
        }

        if ($enc_content !== NULL) {
            $metadata_string = Encryption::decrypt(base64_decode($enc_filedata), $password, $iv[1]);
            $metadata_array = explode(' ', $metadata_string);
            $metadata = ['name' => $metadata_array[0], 'size' => $metadata_array[1], 'type' => $metadata_array[2]];

            $file->setDeletionPassword(base64_decode($metadata_array[3]));
            $file->setMetaData($metadata);
            $file->setContent(Encryption::decrypt(base64_decode($enc_content), $password, $iv[0]));
            return $file;
        }
        return NULL;
    }

    /**
     * Upload a file to the database.
     * @since 2.0
     * @since 2.2 Removed $enc_content, $iv, $enc_filedata & $maxviews from input parameters. Changed output from $id (string) to $file (object).
     * @global array $conf Configuration variables.
     * @global object $mysql_connection MySQL connection.
     * @param object $file File to upload.
     * @param string $password Password to encrypt the file content and metadata with.
     * @return boolean Returns TRUE if the action was successfully executed, otherwise FALSE.
     */
    public static function uploadFile(File $file, string $password) {
        global $conf;
        global $mysql_connection;
        $iv = ['content' => Encryption::getIV(), 'metadata' => Encryption::getIV()];
        $maxviews = $file->getMaxViews();
        $enc_filecontent = Encryption::encryptFileContent($file->getContent(), $password, $iv['content']);
        $enc_filemetadata = Encryption::encryptFileDetails($file->getMetaData(), $file->getDeletionPassword(), $password, $iv['metadata']);
        $enc_iv = base64_encode(implode(',', $iv));
        $null;

        if ($maxviews !== NULL) {
            $enc_maxviews = base64_encode('[0,' . $maxviews . ']');
        } else {
            $enc_maxviews = NULL;
        }

        try {
            $query = $mysql_connection->prepare("INSERT INTO `" . $conf['mysql-table'] . "` (id, iv, metadata, content, maxviews) VALUES (?, ?, ?, ?, ?)");
            if (!$query)
                    throw new Exception('prepare() failed: ' . htmlspecialchars($mysql_connection->error));

            $id = $file->getID();

            $bp = $query->bind_param("sssbs", $id, $enc_iv, $enc_filemetadata, $null, $enc_maxviews);
            if (!$bp)
                    throw new Exception('bind_param() failed: ' . htmlspecialchars($query->error));

            //send content blob to query
            if (!$query->send_long_data(3, $enc_filecontent))
                    throw new Exception('bind_param() failed: ' . htmlspecialchars($query->error));

            if (!$query->execute())
                    throw new Exception('execute() failed: ' . htmlspecialchars($query->error));

            $query->close();
            return true;
        } catch (Exception $e) {
            error_log($e);
            return false;
        }
    }

}
