<?php

set_error_handler('exceptions_error_handler');

function exceptions_error_handler($severity, $message, $filename, $lineno) {
    if (error_reporting() == 0) {
        return;
    }
    if (error_reporting() & $severity) {
        throw new ErrorException($message, 0, $severity, $filename, $lineno);
    }
}

header('Content-Type: application/json; charset=utf-8');
include __DIR__ . '/res/init.php';
global $conf;

function sendOutput($output) {
    return print(json_encode($output, JSON_PRETTY_PRINT));
}

$url = explode('/', strtolower(filter_input(INPUT_SERVER, 'REQUEST_URI')));

$success = false;
$output = array('success' => $success);
if (count($url)) {
    if ($url[2] == 'upload') {
        upload($conf);
    } elseif ($url[2] == 'delete') {
        delete();
      }elseif ($url[2] == 'download'){
        download();
    } else {
        $output['error'] = 'Incorrectly formatted URL.';
        sendOutput($output);
    }
} else {
    $output['error'] = 'Incorrectly formatted URL.';
    sendOutput($output);
}

function upload($conf) {
    if (isset($_FILES['file']) && $_FILES['file'] != NULL) {
        $file = $_FILES['file'];
        $maxsize = Misc::convertToBytes($conf['max-file-size']);
        if ($file['size'] <= $maxsize) {
            $maxviews = Misc::getVar('maxviews');
            if (Misc::getVar('password') != NULL) {
                $password = Misc::getVar('password');
            } else {
                $password = Misc::generatePassword(6, 20);
            }
            $deletionpass = Misc::generatePassword(12, 32);
            $id = DataStorage::getID($file, $password, $maxviews, $deletionpass);
            if (is_bool($id[0]) && $id[0]) {
                $protocol = "http";
                $https = filter_input(INPUT_SERVER, 'HTTPS');
                if (isset($https) && $https !== 'off') {
                    $protocol = "https";
                }
                $completeURL = $protocol . "://" . filter_input(INPUT_SERVER, 'HTTP_HOST') . "/download/" . $id[1] . "/?p=" . urlencode($password);
                $output['success'] = true;
                $output['url'] = $completeURL;
                $output['id'] = $id[1];
                $output['deletepassword'] = $deletionpass;
                if (Misc::getVar('password') == NULL) {
                    $output['password-mode'] = 'Server generated.';
                } else {
                    $output['password-mode'] = 'User generated.';
                }
                $output['password'] = $password;
                if ($maxviews != NULL) {
                    $output['maxviews'] = (int) $maxviews;
                }
            } else {
                $output['error'] = $id[1];
            }
        } else {
            $output['error'] = 'File size exceeded the limit of ' . $conf['max-file-size'] . ".";
        }
    } else {
        $output['error'] = 'No file supplied.';
    }
    sendOutput($output);
}

function delete() {
    $id = Misc::getVar('id');
    $password = Misc::getVar('p');
    $deletionpass = Misc::getVar('delete');
    try {
        $e = DataStorage::getFile($id, $password);
        $delpass = base64_decode(explode(" ", $e[0])[3]);
    } catch (Exception $ex) {
        $output['success'] = false;
        if ($ex->getMessage() == "Undefined offset: 3") {
            $output['error'] = "Bad ID or Password.";
        }
        sendOutput($output);
        return;
    }
    $output = NULL;
    if ($delpass == $deletionpass) {
        error_log("deleting");
        $output['success'] = DataStorage::deleteFile($id);
    } else {
        $output['success'] = false;
        $output['password'] = $delpass[3];
    }
    sendOutput($output);
}

function download(){
      $e = DataStorage::getFile(Misc::getVar("id"), Misc::getVar("p")); # Returns [0] = File Meta Data, [1] = File Content.
      if ($e[0] != NULL) { //successful decryption
        $metadata = explode(" ", $e[0]); # Returns [0] = File Name, [1] = File Length, [2] = File Type, [3] = Deletion Password.
        $output['success'] = true;
        $output['data'] = base64_encode($e[1]);
        $output['type'] = base64_decode($metadata[2]);
        $output['filename'] = base64_decode($metadata[0]);
        $output['length'] = base64_decode($metadata[1]);
        $viewsArray = $e[1];
        if (is_array($viewsArray)) {
            compareViews($viewsArray[0], $viewsArray[1], $url[2]);
            $output['currentviews']=$viewsArray[0];
            $output['maxviews']=$viewsArray[1];
        }
    }else{
      $output['error']="Decryption failed. Is the ID or password wrong?";
    }
    sendOutput($output);
    exit();
}

/**
 * Compare max views with current views-
 * @param int $currentviews Current views.
 * @param int $maxviews Maximum allowed views.
 * @param string $id ID.
 * @return boolean Returns true if current views surpass the maximum views. Otherwise returns false.
 */
function compareViews($currentviews, $maxviews, $id) {
    if (($currentviews + 1) >= $maxviews) {
        return DataStorage::deleteFile($id);
    } else {
        return DataStorage::setViews(intval($maxviews), ($currentviews + 1), $id);
    }
    return false;
}
