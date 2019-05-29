<?php

namespace com\carlgo11\tempfiles;

use com\carlgo11\tempfiles;
use PHPUnit\Framework\TestCase;

class DataStorageTest extends TestCase
{

    // TODO: Remove. Just an example.
    public function testGetMariaDBVersion() {
        $this->assertIsString(DataStorage::getMariaDBVersion());
    }

}
