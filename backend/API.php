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
 *
 * @example sendOutput(['message' => 'A message to output.']);
 * @since 2.0
 * @param array $output Messages to output. ID of the cells should be the same as the desired tag in the JSON output.
 * @param int $responseCode Response code to use when outputting the JSON data.
 * @return boolean Returns TRUE if the action was successfully executed, otherwise FALSE.
 */
function sendOutput(array $output, int $responseCode = 200) {
    http_response_code($responseCode);
    return print(json_encode($output, JSON_PRETTY_PRINT));
}

$url = explode('/', strtolower(filter_input(INPUT_SERVER, 'REQUEST_URI')));

$success = FALSE;
$output = ['success' => $success];
try {

    if (count($url)) {
        if ($url[2] == 'upload') {
            upload($conf);
        } else if ($url[2] == 'delete') {
            delete();
        } else if ($url[2] == 'download') {
            download();
        } else if ($url[2] == 'cleanup') {
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
    $output['error'] = $e->getMessage();
    sendOutput($output, 400);
}

/**
 * Uploads a file to the database.
 *
 * @param $conf config.php array.
 * @throws Exception Throws and exception in case the upload didn't succeed.
 * @since 2.0
 * @since 2.1 Updated to work with v2.1 file handling standard.
 */
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
                    throw new Exception("Upload failed. Either the file is larger than it's supposed to be or the upload was interrupted.");
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
        throw new Exception('No file supplied.');
    }
    sendOutput($output, 201);
}

/**
 * Deletes a file from the database.
 *
 * @throws Exception
 * @since 2.0
 * @since 2.1 Updated function to work with v2.1 file handling standards.
 */
function delete() {
    $id = Misc::getVar('id');
    $password = Misc::getVar('p');
    $deletionpass = Misc::getVar('delete');

    if ($file = DataStorage::getFile($id, $password))
        $db_deletionpass = $file->getDeletionPassword();
    else throw new Exception("Bad ID or Password.");

    if (password_verify($deletionpass, $db_deletionpass))
        sendOutput(['success' => (boolean)DataStorage::deleteFile($id)]);
}

/**
 * Gets the binary data for a file stored on the server.
 *
 * @since 2.0
 * @since 2.2 Updated function to work with v2.2 file handling standards.
 *
 */
function download() {
    $id = Misc::getVar('id');
    $p = Misc::getVar('p');
    $file = DataStorage::getFile($id, $p);
    if (isset($file)) {
        $metadata = $file->getMetaData();
        $content = base64_encode($file->getContent());
        sendOutput([
            "success" => TRUE,
            "type" => base64_decode($metadata['type']),
            "filename" => base64_decode($metadata['name']),
            "length" => base64_decode($metadata['size']),
            "data" => $content
        ], 200);

        Misc::compareViews($file->getCurrentViews(), $file->getMaxViews(), $file->getID());
    } else {
        throw new Exception("File not found");
    }
}

/**
 * Cleans up the database by removing all files older than 24 hours.
 *
 * @since 2.1
 */
function cleanup() {
    $output['success'] = filter_var(DataStorage::deleteOldFiles(), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    sendOutput($output, 202);
}
