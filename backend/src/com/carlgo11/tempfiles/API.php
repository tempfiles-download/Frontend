<?php

namespace com\carlgo11\tempfiles\api;
class API
{
    protected $_messages = [];

    public function addMessage(string $key, $value) {
        return ($this->_messages[$key] = $value) === $value;
    }

    /**
     * @param array $messages
     * @return bool
     * @deprecated Use API::addMessage() instead.
     */
    public function addMessages(array $messages) {
        return ($this->_messages = array_merge($this->_messages, $messages)) !== NULL;
    }

    public function getMessages() {
        return $this->_messages;
    }

    public function removeMessage(string $key) {
        unset($this->_messages[$key]);
        return TRUE;
    }

    public function outputJSON(int $HTTPCode = 200) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($HTTPCode);
        return print(json_encode($this->getMessages(), JSON_PRETTY_PRINT));
    }
}
