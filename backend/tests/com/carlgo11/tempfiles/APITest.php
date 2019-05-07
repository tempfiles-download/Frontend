<?php

namespace com\carlgo11\tempfiles;

use com\carlgo11\tempfiles\api\API;
use PHPUnit\Framework\TestCase;

class APITest extends TestCase
{
    protected $_api;

    public function __construct($name = NULL, array $data = [], $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->_api = new API();
    }

    public function testAddMessage() {
        $this->assertTrue($this->_api->addMessage('testmsg', 'test1234'));
    }

    public function testAddMessages() {
        $this->assertTrue($this->_api->addMessages(['testmsgs' => 'test5678']));
    }

    public function testGetMessages() {
        $this->assertIsArray($this->_api->getMessages());
    }

    public function testRemoveMessage() {
        $this->_api->addMessage('testremove', TRUE);
        $this->assertTrue($this->_api->removeMessage('testremove'));
    }
}
