<?php

namespace com\carlgo11\tempfiles;
class File
{

    protected $_id;
    protected $_content;
    protected $_currentViews;
    protected $_maxViews;
    protected $_deletionPassword;
    protected $_metaData;
    protected $_iv;

    /**
     * Main function of File class.
     *
     * @param array $file $_FILES array if available.
     * @param string $id ID if one is already set.
     * @since 2.2
     */
    public function __construct($file, string $id = NULL) {
        if ($id === NULL) $this->generateID();
        else $this->setID($id);

        if ($file !== NULL)
            $this->_metadata = ['name' => $file['name'], 'size' => $file['size'], 'type' => $file['type']];
    }

    /**
     * Gets ID of the file.
     *
     * @return string Returns the ID of the file.
     * @since 2.2
     */
    public function __toString() {
        return $this->_id;
    }

    /**
     * Gets ID of the file.
     *
     * @return string Returns the ID of the file.
     * @since 2.2
     */
    public function getID() {
        return $this->_id;
    }

    /**
     * Gets file content.
     *
     * @return string Returns file content in clear text.
     * @since 2.2
     */
    public function getContent() {
        return $this->_content;
    }

    /**
     * Gets the current views of the file if available.
     *
     * @return mixed Returns current views/downloads of the file if supplied, otherwise NULL.
     * @since 2.2
     */
    public function getCurrentViews() {
        return $this->_currentViews;
    }

    /**
     * Gets the max available views/downloads before the file gets deleted.
     *
     * @return mixed Returns max views of the file if supplied, otherwise NULL.
     * @since 2.2
     */
    public function getMaxViews() {
        return $this->_maxViews;
    }

    /**
     * Gets the deletion password of the file.
     *
     * @return string Returns deletion password if supplied, otherwise NULL.
     * @since 2.2
     */
    public function getDeletionPassword() {
        return $this->_deletionPassword;
    }

    /**
     * Gets the metadata of the file if supplied.
     *
     * @param string $type Array key of the desired value.
     * @return mixed Returns data of the desired array key if a $type is supplied, otherwise the entire array.
     * @since 2.2
     */
    public function getMetaData(string $type = NULL) {
        if ($type !== NULL) return $this->_metadata[$type];
        return $this->_metaData;
    }

    public function getIV() {
        return $this->_iv;
    }

    /**
     * Sets the ID of the file.
     *
     * @param string $id New ID of the file.
     * @return boolean Returns TRUE if the action was successfully executed, otherwise FALSE.
     * @since 2.2
     */
    private function setID(string $id) {
        return ($this->_id = $id) === $id;
    }

    /**
     * Generates a new ID for the file.
     *
     * @return boolean Returns TRUE if the action was successfully executed, otherwise FALSE.
     * @since 2.2
     */
    private function generateID() {
        return is_string($this->_id = strtoupper(uniqid("d")));
    }

    /**
     * Sets the current views/downloads of the file.
     *
     * @param int $views New views/downloads of the file.
     * @return boolean Returns TRUE if the action was successfully executed. Returns FALSE if the file was deleted. Returns NULL if currentsViews wasn't set.
     * @since 2.2
     */
    public function setCurrentViews(int $views) {
        if ($this->_maxViews != 0) {
            if ($views < $this->_maxViews) {
                $this->_currentViews = $views;
                return TRUE;
            } else if ($views >= $this->_maxViews) {
                DataStorage::deleteFile($this->_id);
                return FALSE;
            }
        }
    }

    /**
     * Sets the max views of the file.
     *
     * @param int $views New max views of the file.
     * @return boolean Returns TRUE if the action was successfully executed, otherwise FALSE.
     * @since 2.2
     */
    public function setMaxViews(int $views) {
        return ($this->_maxViews = $views) === $views;
    }

    /**
     * Sets the metadata of the file.
     *
     * @param array New metadata of the file.
     * @return boolean Returns TRUE if the action was successfully executed, otherwise FALSE.
     * @since 2.2
     */
    public function setMetaData(array $metadata) {
        return ($this->_metaData = $metadata) === $metadata;
    }

    /**
     * Sets the deletion password of the file.
     *
     * @param string $deletionpassword New deletion password of the file.
     * @return boolean Returns TRUE if the action was successfully executed, otherwise FALSE.
     * @since 2.2
     */
    public function setDeletionPassword(string $deletionpassword) {
        return ($this->_deletionPassword = $deletionpassword) === $deletionpassword;
    }

    /**
     * Sets the content of the file.
     *
     * @param string $content New content of the file. Should be sent as clear text.
     * @return boolean Returns TRUE if the action was successfully executed, otherwise FALSE.
     * @since 2.2
     */
    public function setContent($content) {
        return ($this->_content = $content) === $content;
    }

    public function setIV(array $iv) {
        return ($this->_iv = $iv) === $iv;
    }

}
