<?php

class File {

    protected $_id;
    protected $_content;
    protected $_currentViews;
    protected $_maxViews;
    protected $_deletionPassword;
    protected $_metaData;

    public function __construct(array $file, string $id = NULL) {
        if ($id === NULL) {
            $this->generateID();
        } else {
            $this->setID($id);
        }
        if ($file !== NULL) {
            $this->_metadata = ['name' => $file['name'], 'size' => $file['size'], 'type' => $file['type']];
        }
    }

    public function __toString() {
        return $this->_id;
    }

    public function getID() {
        return $this->_id;
    }

    public function getContent() {
        return $this->_content;
    }

    public function getCurrentViews() {
        return $this->_currentViews;
    }

    public function getMaxViews() {
        return $this->_maxViews;
    }

    public function getDeletionPassword() {
        return $this->_deletionPassword;
    }

    /**
     *
     */
    public function getMetaData(string $type = NULL) {
        if ($type !== NULL) return $this->_metadata[$type];
        return $this->_metaData;
    }

    private function setID(string $id) {
        return $this->_id = $id;
    }

    public function generateID() {
        $id = strtoupper(uniqid("d"));
        return $this->_id = $id;
    }

    public function setCurrentViews(int $views) {
        if ($views > $this->_maxViews) {
            DataStorage::setViews($this->_maxViews, $views, $this->getID());
            return $this->_currentViews = $views;
        } elseif ($views <= $this->maxViews) {
            DataStorage::deleteFile($this->_id);
        }
    }

    public function setMaxViews(int $views) {
        $this->_maxViews;
    }

    public function setMetaData(array $metadata) {
        $this->_metaData = $metadata;
    }

    public function setDeletionPassword(string $deletionpassword) {
        $this->_deletionPassword = $deletionpassword;
    }

    public function setContent($content) {
        $this->_content = $content;
    }

}
