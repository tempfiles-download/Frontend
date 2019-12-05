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

            if ($file->setCurrentViews(($file->getCurrentViews() + 1)))
                DataStorage::setViews($file->getMaxViews(), ($file->getCurrentViews() + 1), $file, $p);

        } else {
            throw new Exception("File not found");
        }
    }
}
