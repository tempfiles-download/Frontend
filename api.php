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

$url = explode('/', strtolower($_SERVER['REQUEST_URI']));
$success = false;
$output = array('success' => $success);
if (count($url)) {
    if ($url[2] == 'upload') {
        upload($conf);
    } elseif ($url[2] == 'delete') {
        delete();
    } else {
        $output['error'] = 'Incorrectly formatted URL.';
        sendOutput($output);
    }
} else {
    $output['error'] = 'Incorrectly formatted URL.';
    sendOutput($output);
}

function upload($conf) {
    if ($_FILES['file'] != NULL) {
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
                $completeURL = 'https://tempfiles.carlgo11.com/d/' . $id[1] . '/?p=' . urlencode($password);
                $output['success'] = true;
                $output['url'] = $completeURL;
                $output['deletepassword'] = $deletionpass;
                if (Misc::getVar('password') == NULL) {
                    $output['passowrd-mode'] = 'Server generated.';
                } else {
                    $output['passowrd-mode'] = 'User generated.';
                }
                $output['passowrd'] = $password;
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
