<?php

namespace com\carlgo11\tempfiles\api;
use com\carlgo11\tempfiles\DataStorage;
use com\carlgo11\tempfiles\Misc;
use Exception;

class Delete extends API
{
    public function __construct() {
        $id = Misc::getVar('id');
        $password = Misc::getVar('p');
        $deletionPassword = Misc::getVar('delete');

        if ($file = DataStorage::getFile($id, $password))
            $db_deletionPassword = $file->getDeletionPassword();
        else throw new Exception("Bad ID or Password.");

        if (password_verify($deletionPassword, $db_deletionPassword)) {
            parent::addMessage('success', (boolean)DataStorage::deleteFile($id));
            return parent::outputJSON(200);
        }
        throw new Exception("Bad ID or Password.");
    }
}