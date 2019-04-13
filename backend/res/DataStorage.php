<?php

/**
 * DataStorage handles all MySQL database connecting functions.
 *
 * @since 2.0
 */
class DataStorage
{

    /**
     * Set the current views and max views for a specified file.
     * When the current views are equal or exceeds max views the file will be deleted and the user will get a 404 error.
     *
     * @param string $maxViews The new maximum amount of views to set on a file.
     * @param string $newViews The new current views to set on a file.
     * @return boolean Returns TRUE if the change was successful, otherwise FALSE.
     * @since 2.0
     * @global array $conf Configuration variables.
     * @global object $mysql_connection MySQL connection.
     */
    public static function setViews(string $maxViews, string $newViews, File $file, string $password) {
        global $conf;
        global $mysql_connection;
        $id = $file->getID();
        $enc_filemetadata = Encryption::encryptFileDetails($file->getMetaData(), $file->getDeletionPassword(), $newViews, $maxViews, $password, $file->getIV()[1]);
        $query = $mysql_connection->prepare("UPDATE `" . $conf['mysql-table'] . "` SET `metadata` = ? WHERE `id` = ?");
        if (!$query->bind_param("ss", $enc_filemetadata, $id)) {
            error_log('bind_param() failed: ' . htmlspecialchars($query->error));
            return FALSE;
        }
        $result = $query->execute();
        $query->close();
        return $result;
    }

    /**
     * Delete a specific file.
     *
     * @param string $id The ID for the file.
     * @return boolean Returns TRUE if the deletion was successful, otherwise FALSE.
     * @global object $mysql_connection MySQL connection.
     * @since 2.0
     * @global array $conf Configuration variables.
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
     *
     * @return boolean Returns TRUE if the query was successful, otherwise FALSE.
     * @since 2.1
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
     *
     * @param string $id The ID for the file.
     * @param string $password Description
     * @return mixed Returns the downloaded and decrypted file (object) if successfully downloaded and decrypted, otherwise NULL (boolean).
     * @since 2.0
     * @since 2.3 Add support for AEAD cipher modes.
     * @global array $conf Configuration variables.
     * @global object $mysql_connection MySQL connection.
     */
    public static function getFile(string $id, string $password) {
        global $conf;
        global $mysql_connection;
        require_once __DIR__ . '/File.php';

        $query = $mysql_connection->prepare("SELECT `iv`, `metadata`, `content` FROM `" . $conf['mysql-table'] . "` WHERE `id` = ?");
        $query->bind_param("s", $id);
        $query->execute();
        $query->store_result();
        $query->bind_result($iv_encoded, $enc_filedata, $enc_content);
        $query->fetch();
        $query->close();

        $file = new File(NULL, $id);
        $iv = explode(",", base64_decode($iv_encoded));
        $file->setIV($iv);

        if ($enc_content !== NULL) {
            $metadata_string = Encryption::decrypt(base64_decode($enc_filedata), $password, $iv[2], $iv[3]);

            /** @var array $metadata_array
             * Array containing the following: [name, size, type, deletionPassword, views_array[ 0 => currentViews, 1 => maxViews]]
             */
            $metadata_array = explode(' ', $metadata_string);
            $metadata = ['name' => $metadata_array[0], 'size' => $metadata_array[1], 'type' => $metadata_array[2]];
            $views_array = explode(' ', base64_decode($metadata_array[4]));

            $file->setDeletionPassword(base64_decode($metadata_array[3]));
            $file->setMetaData($metadata);
            $file->setContent(Encryption::decrypt(base64_decode($enc_content), $password, $iv[0], $iv[1]));
            $file->setMaxViews((int)$views_array[1]);
            $file->setCurrentViews((int)$views_array[0]);

            return $file;
        }
        return NULL;
    }

    /**
     * Upload a file to the database.
     *
     * @param File $file File to upload.
     * @param string $password Password to encrypt the file content and metadata with.
     * @return boolean Returns TRUE if the action was successfully executed, otherwise FALSE.
     * @global object $mysql_connection MySQL connection.
     * @since 2.0
     * @since 2.2 Removed $enc_content, $iv, $enc_filedata & $maxviews from input parameters. Changed output from $id (string) to $file (object).
     * @since 2.3 AAdd support for AEAD cipher modes.
     * @global array $conf Configuration variables.
     */
    public static function uploadFile(File $file, string $password) {
        global $conf;
        global $mysql_connection;
        $fileContent = Encryption::encryptFileContent($file->getContent(), $password);
        $fileMetadata = Encryption::encryptFileDetails($file->getMetaData(), $file->getDeletionPassword(), 0, $file->getMaxViews(), $password);
        $iv = [$fileContent['iv'], $fileContent['tag'], $fileMetadata['iv'], $fileMetadata['tag']];
        $enc_iv = base64_encode(implode(',', $iv));
        $null = NULL;

        try {
            $query = $mysql_connection->prepare("INSERT INTO `" . $conf['mysql-table'] . "` (id, iv, metadata, content) VALUES (?, ?, ?, ?)");
            if (!$query)
                throw new Exception('prepare() failed: ' . htmlspecialchars($mysql_connection->error));

            $id = $file->getID();

            $bp = $query->bind_param("sssb", $id, $enc_iv, $fileMetadata['data'], $null);
            if (!$bp)
                throw new Exception('bind_param() failed: ' . htmlspecialchars($query->error));

            // Replace $null with content blob
            if (!$query->send_long_data(3, $fileContent['data']))
                throw new Exception('bind_param() failed: ' . htmlspecialchars($query->error));

            if (!$query->execute())
                throw new Exception('execute() failed: ' . htmlspecialchars($query->error));

            $query->close();
            return TRUE;
        } catch (Exception $e) {
            error_log($e);
            return FALSE;
        }
    }

}
