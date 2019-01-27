<?php

/**
 * Encryption handling.
 *
 * @since 2.0
 */
class Encryption
{

    /**
     * Decrypts data.
     *
     * @since 2.0
     * @global array $conf Configuration variables.
     * @param string $data Data to decrypt.
     * @param string $password Password used to decrypt.
     * @param string $iv IV for decryption.
     * @return string Returns decrypted data.
     */
    public static function decrypt(string $data, string $password, string $iv) {
        global $conf;
        return openssl_decrypt($data, $conf['Encryption-Method'], $password, OPENSSL_RAW_DATA, $iv);
    }

    /**
     * Encrypts and encodes the metadata (details) of a file.
     *
     * @since 2.0
     * @since 2.2 Added $deletionpass to the array of things to encrypt.
     * @param array $file the $_FILES[] array to use.
     * @param string $deletionpass Deletion password to encrypt along with the metadata.
     * @param int $currentViews Current views of the file.
     * @param int $maxViews Max allowable views of the file before deletion.
     * @param string $password Password used to encrypt the data.
     * @param string $iv IV used to encrypt the data.
     * @return string Returns encrypted file metadata encoded with base64.
     * @global array $conf Configuration variables.
     */
    public static function encryptFileDetails(array $file, string $deletionpass, int $currentViews, $maxViews, string $password, string $iv) {
        global $conf;
        $deletionpass = password_hash($deletionpass, PASSWORD_BCRYPT);
        $views_string = implode(' ', [$currentViews, $maxViews]);
        $data_array = [
            base64_encode($file['name']),
            base64_encode($file['size']),
            base64_encode($file['type']),
            base64_encode($deletionpass),
            base64_encode($views_string)
        ];
        $data_string = implode(" ", $data_array);
        $data_enc = openssl_encrypt($data_string, $conf['Encryption-Method'], $password, OPENSSL_RAW_DATA, $iv);
        return base64_encode($data_enc);
    }

    /**
     * Encrypts and encodes the content (data) of a file.
     *
     * @since 2.0
     * @global array $conf Configuration variables.
     * @param string $content Data to encrypt.
     * @param string $password Password used to encrypt data.
     * @param string $iv IV used to encrypt data.
     * @return string Returns encoded and encrypted file content.
     */
    public static function encryptFileContent(string $content, string $password, string $iv) {
        global $conf;
        return base64_encode(openssl_encrypt($content, $conf['Encryption-Method'], $password, OPENSSL_RAW_DATA, $iv));
    }

    /**
     * Create an IV (Initialization Vector) string.
     * IV contains of random data from a "random" source. In this case the source is OPENSSL.
     *
     * @since 2.0
     * @return string Returns an IV string encoded with base64.
     */
    public static function getIV() {
        return mb_strcut(base64_encode(openssl_random_pseudo_bytes(16)), 0, 16);
    }

}
