<?php

namespace com\carlgo11\tempfiles;

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testArrayContents(){
        global $conf;
        $this->assertIsArray($conf);

        $vars = ['max-file-size', 'mysql-url', 'mysql-user', 'mysql-password', 'mysql-db', 'mysql-table', 'Encryption-Method'];
        foreach ($vars as $var){
            $this->assertArrayHasKey($var, $conf, 'Config.php doesn\'t include the key "'.$var.'".');
        }
    }

}
