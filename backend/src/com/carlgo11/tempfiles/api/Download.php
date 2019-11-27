<?php

namespace com\carlgo11\tempfiles\api;

use com\carlgo11\tempfiles\DataStorage;
use com\carlgo11\tempfiles\Misc;
use Exception;

class Download extends API
{

    /**
     * Download constructor.
     *
     * @param string $method HTTP method.
     * @throws Exception Throws exception if HTTP method is invalid.
     */
    public function __construct(string $method) {
        if ($method !== 'GET') throw new Exception("Bad method. Use GET.");

        $id = Misc::getVar('id');
        $p = Misc::getVar('p');
        $file = DataStorage::getFile($id, $p);
        if (isset($file)) {
            $metadata = $file->getMetaData();
            $content = base64_encode($file->getContent());
            parent::addMessages([
                "success" => TRUE,
                "type" => base64_decode($metadata['type']),
                "filename" => base64_decode($metadata['name']),
                "length" => base64_decode($metadata['size']),
                "data" => $content
            ]);
            parent::outputJSON(200);
            Misc::compareViews($file->getCurrentViews(), $file->getMaxViews(), $file->getID());
        } else {
            throw new Exception("File not found");
        }
    }
}