<?php

namespace com\carlgo11\tempfiles\api;

use com\carlgo11\tempfiles\DataStorage;
use com\carlgo11\tempfiles\File;
use com\carlgo11\tempfiles\Misc;
use Exception;
use InvalidArgumentException;

class Upload extends API
{
    /**
     * Upload constructor.
     *
     * @throws Exception
     */
    function __construct() {
        global $conf;

        if (isset($_FILES['file']) && $_FILES['file'] !== NULL) {
            $fileContent = $_FILES['file'];
            $file = new File($fileContent);

            if (Misc::getVar('maxviews') !== NULL)
                $file->setMaxViews(Misc::getVar('maxviews'));
            if (Misc::getVar('password') !== NULL) {
                $password = Misc::getVar('password');
            } else {
                $password = Misc::generatePassword(6, 20);
            }

            $file->setDeletionPassword(Misc::generatePassword(12, 32));

            $metadata = [
                'size' => $fileContent['size'],
                'name' => $fileContent['name'],
                'type' => $fileContent['type']
            ];
            $file->setMetaData($metadata);

            if ($file->getMetaData('size') <= Misc::convertToBytes($conf['max-file-size'])) {
                if ($password !== NULL) {
                    if ($fileContent['error'] === 0) {
                        $file->setContent(file_get_contents($fileContent['tmp_name']));
                        if (!($upload = DataStorage::uploadFile($file, $password))) {
                            throw new Exception("Connection to our database failed.");
                        }
                    } else {
                        throw new InvalidArgumentException("Upload failed. Either the file is larger than it's supposed to be or the upload was interrupted.");
                    }
                } else {
                    throw new InvalidArgumentException("Password not set.");
                }
            } else {
                throw new InvalidArgumentException("File size too large. Maximum allowed " . $conf['max-file-size'] . " (currently " . $file->getMetaData('size') . ")");
            }

            if (is_bool($upload) && $upload) {
                $protocol = "http";
                $https = filter_input(INPUT_SERVER, 'HTTPS');
                if (isset($https) && $https !== 'off') {
                    $protocol = "https";
                }

                // Full URI to download the file
                $completeURL = $protocol . "://" . filter_input(INPUT_SERVER, 'HTTP_HOST') . "/download/" . $file->getID() . "/?p=" . urlencode($password);

                $output['success'] = TRUE;
                $output['url'] = $completeURL;
                $output['id'] = $file->getID();
                $output['deletepassword'] = $file->getDeletionPassword();

                if (Misc::getVar('password') === NULL) {
                    $output['password-mode'] = 'Server generated.';
                } else {
                    $output['password-mode'] = 'User generated.';
                }

                $output['password'] = $password;

                if ($file->getMaxViews() !== NULL) {
                    $output['maxviews'] = (int)$file->getMaxViews();
                }
            } else {
                throw new Exception($upload);
            }
        } else {
            throw new InvalidArgumentException('No file supplied.');
        }
        parent::addMessages($output);
        return parent::outputJSON(201);
    }
}