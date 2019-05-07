<?php

namespace com\carlgo11\tempfiles;

use PHPUnit\Framework\TestCase;

class EncryptionTest extends TestCase
{

    public function testGetIV() {
        global $conf;
        $this->assertIsString(Encryption::getIV($conf['Encryption-Method']));
    }

    public function testEncryptFileContent() {
        $content = 'so3CMoN7H5U4F0fPRQ0eob462DVc9k1VL1v';
        $password = 'evO07HL470qdv5d7AyzQ6NgTk94dNUj4v4K';

        $enc_content = Encryption::encryptFileContent($content, $password);

        $this->assertIsArray($enc_content);
        $keys = ['data', 'iv', 'tag'];

        foreach ($keys as $k) {
            $this->assertArrayHasKey($k, $enc_content);
            $this->assertIsString($enc_content[$k]);
        }

        $this->assertIsString(Encryption::decrypt($enc_content['data'], $password, $enc_content['iv'], $enc_content['tag']));
    }

//    public function testEncryptFileDetails() {
//
//    }
}
