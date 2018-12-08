<?php

/**
 * Encryption handling.
 * @since 2.0
 */
class Encryption {

    /**
     * Decrypts data.
     * @global array $conf Configuration variables.
     * @param string $data Data to decrypt.
     * @param string $password Password used to decrypt.
     * @param string $iv IV for decryption.
     * @return string Decrypted data.
     */
    public static function decrypt(string $data, string $password, string $iv) {
        global $conf;
        return openssl_decrypt($data, $conf['Encryption-Method'], $password, OPENSSL_RAW_DATA, $iv);
    }

    /**
     * Encrypts and encodes the metadata (details) of a file.
     * @global array $conf Configuration variables.
     * @param string $filename File name.
     * @param string $filesize File Size.
     * @param string $filetype File type.
     * @param string $password Password used to encrypt the data.
     * @param string $iv IV used to encrypt the data.
     * @return string Returns encoded and encrypted file metadata.
     */
    public static function encryptFileDetails(array $file, string $deletionpass, $password, $iv) {
        global $conf;
        $dataarray = array(base64_encode($file['name']), base64_encode($file['size']), base64_encode($file['type']), base64_encode($deletionpass));
        $filedata = implode(" ", $dataarray);
        $enc_data = openssl_encrypt($filedata, $conf['Encryption-Method'], $password, OPENSSL_RAW_DATA, $iv);
        return base64_encode($enc_data);
    }

    /**
     * Encrypts and encodes the content (data) of a file.
     * @global array $conf Configuration variables.
     * @param string $content Data to encrypt.
     * @param string $password Password used to encrypt data.
     * @param string $iv IV used to encrypt data.
     * @return string Returns encoded and encrypted file content.
     */
    public static function encryptFileContent($content, $password, $iv) {
        global $conf;
        $enc_content = openssl_encrypt($content, $conf['Encryption-Method'], $password, OPENSSL_RAW_DATA, $iv);
        return base64_encode($enc_content);
    }

    /**
     * Create an IV (Initialization Vector) string.
     * IV contains of random data from a "random" source. In this case the source is openssl.
     * @return string Returns an IV string encoded with base64.
     */
    public static function getIV() {
        return mb_strcut(base64_encode(openssl_random_pseudo_bytes(16)), 0, 16);
    }

}
