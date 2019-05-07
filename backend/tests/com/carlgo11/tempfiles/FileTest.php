<?php

namespace com\carlgo11\tempfiles;

use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{

    protected $_file;

    public function __construct($name = NULL, array $data = [], $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->_file = new File(NULL);

    }

    public function testIV() {
        global $conf;

        $iv = [Encryption::getIV($conf['Encryption-Method']), Encryption::getIV($conf['Encryption-Method'])];
        $this->assertTrue($this->_file->setIV($iv));
        $this->assertEquals($iv, $this->_file->getIV());
    }

    public function testMaxViews() {
        $maxViews = 3;
        $this->assertTrue($this->_file->setMaxViews($maxViews));
        $this->assertEquals($maxViews, $this->_file->getMaxViews());

    }

    public function testContent() {
        $content = 'ijf8z388cbbbX9GFnle45lUVw52W1Z';
        $this->assertTrue($this->_file->setContent($content));
        $this->assertEquals($content, $this->_file->getContent());
    }

    public function testCurrentViews() {
        $currentViews = 0;
        // FALSE = views have exceeded max views and file should be deleted.
        $this->assertNotFalse($this->_file->setCurrentViews($currentViews));
        $this->assertEquals($currentViews, $this->_file->getCurrentViews());
    }


//    public function testMetaData() {
//
//    }
//
//
    public function testDeletionPassword() {
        $deletionPassword = '27DTaEw1eK1rmJ63RKjsq8N1Sp8Mm4';
        $this->assertTrue($this->_file->setDeletionPassword($deletionPassword));
        $this->assertEquals($deletionPassword, $this->_file->getDeletionPassword());

    }

    public function testGetID() {
        $this->assertNotNull($this->_file->getID());

    }
}
