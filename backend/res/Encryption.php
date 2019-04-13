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
     * @param string $data Data to decrypt.
     * @param string $password Password used to decrypt.
     * @param string $iv IV for decryption.
     * @param string $tag AEAD tag from the data encryption.
     * @return string Returns decrypted data.
     * @since 2.0
     * @since 2.3 Added support for AEAD cipher modes.
     * @global array $conf Configuration variables.
     */
    public static function decrypt(string $data, string $password, string $iv, string $tag = NULL) {
        global $conf;
        return openssl_decrypt($data, $conf['Encryption-Method'], $password, OPENSSL_RAW_DATA, $iv, $tag);
    }

    /**
     * Encrypts and encodes the metadata (details) of a file.
     *
     * @param array $file the $_FILES[] array to use.
     * @param string $deletionpass Deletion password to encrypt along with the metadata.
     * @param int $currentViews Current views of the file.
     * @param int $maxViews Max allowable views of the file before deletion.
     * @param string $password Password used to encrypt the data.
     * @return array Returns encrypted file metadata encoded with base64.
     * @since 2.0
     * @since 2.2 Added $deletionpass to the array of things to encrypt.
     * @since 2.3 Added support for AEAD cipher modes.
     * @global array $conf Configuration variables.
     */
    public static function encryptFileDetails(array $file, string $deletionpass, int $currentViews, $maxViews, string $password) {
        global $conf;
        $cipher = $conf['Encryption-Method'];
        $iv = self::getIV($cipher);

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

        $data_enc = base64_encode(openssl_encrypt($data_string, $cipher, $password, OPENSSL_RAW_DATA, $iv, $tag));
        return ['data' => $data_enc, 'iv' => $iv, 'tag' => $tag];
    }

    /**
     * Encrypts and encodes the content (data) of a file.
     *
     * @param string $content Data to encrypt.
     * @param string $password Password used to encrypt data.
     * @return array Returns encoded and encrypted file content.
     * @since 2.0
     * @since 2.3 Added support for AEAD cipher modes.
     * @global array $conf Configuration variables.
     */
    public static function encryptFileContent(string $content, string $password) {
        global $conf;
        $cipher = $conf['Encryption-Method'];
        $iv = self::getIV($cipher);

        $data = base64_encode(openssl_encrypt($content, $cipher, $password, OPENSSL_RAW_DATA, $iv, $tag));
        return ['data' => $data, 'iv' => $iv, 'tag' => $tag];
    }

    /**
     * Create an IV (Initialization Vector) string.
     * IV contains of random data from a "random" source. In this case the source is OPENSSL.
     *
     * @param string $cipher Encryption method to use.
     * @return string Returns an IV string encoded with base64.
     * @since 2.0
     * @since 2.3 Added support for variable IV length.
     */
    public static function getIV(string $cipher) {
        $ivLength = openssl_cipher_iv_length($cipher);
        return mb_strcut(base64_encode(openssl_random_pseudo_bytes($ivLength)), 0, $ivLength);
    }

}
