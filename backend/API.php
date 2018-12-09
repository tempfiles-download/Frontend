<?php

include_once __DIR__ . '/res/init.php';
global $conf;

header('Content-Type: application/json; charset=utf-8');
set_error_handler('exceptions_error_handler');

function exceptions_error_handler($severity, $message, $filename, $lineno) {
    if (error_reporting() == 0) return;

    if (error_reporting() & $severity)
            throw new ErrorException($message, 0, $severity, $filename, $lineno);
}

/**
 * Converts $output to JSON format and prints to client.
 * Example:
 * sendOutput(['message' => 'A message to output.']);
 * 
 * @since 2.0
 * @param array $output Messages to output. ID of the cells should be the same as the desired tag in the JSON output.
 * @return boolean Returns TRUE if the action was successfully executed, otherwise FALSE.
 */
function sendOutput(array $output) {
    return print(json_encode($output, JSON_PRETTY_PRINT));
}

$url = explode('/', strtolower(filter_input(INPUT_SERVER, 'REQUEST_URI')));

$success = false;
$output = array('success' => $success);
try {

    if (count($url)) {
        if ($url[2] == 'upload') {
            upload($conf);
        } elseif ($url[2] == 'delete') {
            delete();
        } elseif ($url[2] == 'download') {
            download();
        } elseif ($url[2] == 'cleanup') {
            cleanup();
        } else {
            $output['error'] = 'Incorrectly formatted URL.';
            sendOutput($output);
        }
    } else {
        $output['error'] = 'Incorrectly formatted URL.';
        sendOutput($output);
    }
} catch (Exception $e) {
    $output['success'] = false;
    $output['error'] = $e->getMessage();
    error_log($e);
    sendOutput($output);
}

function upload($conf) {
    require_once __DIR__ . '/res/File.php';

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

        $metadata = ['size' => $fileContent['size'], 'name' => $fileContent['name'], 'type' => $fileContent['type']];
        $file->setMetaData($metadata);

        if ($file->getMetaData('size') <= Misc::convertToBytes($conf['max-file-size'])) {
            if ($password !== NULL) {
                $file->setContent(file_get_contents($fileContent['tmp_name']));
                if (!($upload = DataStorage::uploadFile($file, $password))) {
                    throw new Exception("Connection to our database failed.");
                }
            } else {
                throw new Exception("Password not set.");
            }
        } else {
            throw new Exception("File size too large. Maximum allowed " . $conf['max-file-size'] . " (currently " . $file->getMetaData('size') . ")");
        }

        if (is_bool($upload) && $upload) {
            $protocol = "http";
            $https = filter_input(INPUT_SERVER, 'HTTPS');
            if (isset($https) && $https !== 'off') {
                $protocol = "https";
            }

            // Full URI to download the file
            $completeURL = $protocol . "://" . filter_input(INPUT_SERVER, 'HTTP_HOST') . "/download/" . $file->getID() . "/?p=" . urlencode($password);

            $output['success'] = true;
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
                $output['maxviews'] = (int) $file->getMaxViews();
            }
        } else {
            throw new Exception($upload);
        }
    } else {
        throw new Exception('No file supplied.');
    }
    sendOutput($output);
}

function delete() {
    $id = Misc::getVar('id');
    $password = Misc::getVar('p');
    $deletionpass = Misc::getVar('delete');

    if ($file = DataStorage::getFile($id, $password))
            $delpass = $file->getDeletionPassword();
    else throw new Exception("Bad ID or Password.");

    if ($delpass == $deletionpass)
            $output['success'] = DataStorage::deleteFile($id);

    sendOutput($output);
}

/**
 * Gets the binary data for a file stored on the server.
 * @since 2.0
 * 
 */
function download() {
    require_once __DIR__ . '/res/ID.php';

    $file = DataStorage::getFile(new ID(Misc::getVar("id")), Misc::getVar("p"));
    if ($file !== NULL) { //successful decryption
        $metadata = explode(" ", $e[0]); # Returns [0] = File Name, [1] = File Length, [2] = File Type, [3] = Deletion Password.
        $output = [
            "success" => true,
            "type" => base64_decode($file->getMetaData('type')),
            "filename" => base64_decode($file->getMetaData('name')),
            "length" => base64_decode($file->getMetaData('size')),
            "data" => $file->getContent()
        ];

        Misc::compareViews($file->getCurrentViews(), $file->getMaxViews(), $file->getID());
    } else {
        $output['error'] = "Bad ID or Password.";
    }
    sendOutput($output);
    exit();
}

/**
 * Cleans up the database by removing all files older than 24 hours.
 * @since 2.1
 */
function cleanup() {
    $output['success'] = filter_var(DataStorage::deleteOldFiles(), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    sendOutput($output);
}
