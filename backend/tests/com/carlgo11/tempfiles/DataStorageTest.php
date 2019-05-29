<?php

namespace com\carlgo11\tempfiles;

use com\carlgo11\tempfiles\api\DataStorage;
use PHPUnit\Framework\TestCase;

class DataStorageTest extends TestCase
{

    public function testGetMariaDBVersion() {
        $this->assertIsString(DataStorage::getMariaDBVersion());
    }

}
