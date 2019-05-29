<?php

namespace com\carlgo11\tempfiles;

use com\carlgo11\tempfiles;
use PHPUnit\Framework\TestCase;

class DataStorageTest extends TestCase
{

    public function testGetMariaDBVersion() {
        $v = DataStorage::getMariaDBVersion();
        error_log($v);
        $this->assertIsString($v);
    }

}
