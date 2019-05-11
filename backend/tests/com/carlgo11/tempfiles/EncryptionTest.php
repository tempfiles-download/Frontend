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

    public function testEncryptFileDetails() {
        global $conf;

        // Setup initial variables
        $file = ['name' => 'testfile.txt', 'size' => '4096', 'type' => 'text/txt'];
        $delpass = 'gzxHJF4MZd3Ul0KsLo8vb7SPDO';
        $currentViews = 0;
        $maxViews = 9;
        $password = '1VMy5E!71-/R8acDuO8';

        $encrypted = Encryption::encryptFileDetails($file, $delpass, $currentViews, $maxViews, $password);

        // Test $encrypted output
        $this->assertIsArray($encrypted);

        // Test content of $encrypted
        $this->assertIsString($encrypted['data']);
        $this->assertIsString($encrypted['iv']);
        $this->assertIsString($encrypted['tag']);

        $decrypted = explode(" ", Encryption::decrypt($encrypted['data'], $password, $encrypted['iv'], $encrypted['tag']));

        // Test $decrypted output
        $this->assertIsArray($decrypted);

        // Test content of $decrypted
        $this->assertEquals($file['name'], base64_decode($decrypted[0]));
        $this->assertEquals($file['size'], base64_decode($decrypted[1]));
        $this->assertEquals($file['type'], base64_decode($decrypted[2]));
        $this->assertTrue(password_verify($delpass, base64_decode($decrypted[3])));

        // Test view array
        $decrypted_views = explode(" ", base64_decode($decrypted[4]));
        $this->assertEquals($currentViews, $decrypted_views[0]);
        $this->assertEquals($maxViews, $decrypted_views[1]);
    }
}
