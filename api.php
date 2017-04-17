<?php

header('Content-Type: application/json; charset=utf-8');
include __DIR__ . '/res/init.php';

function sendOutput($output) {
  return print(json_encode($output, JSON_PRETTY_PRINT));
}

$url = explode('/', strtolower($_SERVER['REQUEST_URI']));
$success = false;
$output = array('success' => $success);
if (count($url)) {
  if ($url[3] == 'upload') {
    upload();
  } elseif ($url[3] == 'delete') {
    delete();
  } else {
    $output['error'] = 'Incorrectly formatted URL.';
    sendOutput($output);
  }
} else {
  $output['error'] = 'Incorrectly formatted URL.';
  sendOutput($output);
}

function upload() {
  if ($_FILES['file'] != NULL) {
    $file = $_FILES['file'];
    $maxsize = Misc::convertToBytes($config['max-file-size']);
    if ($file['size'] >= $maxsize) {
      $maxviews = Misc::getVar('maxviews');
      if (Misc::getVar('password') != NULL) {
        $password = Misc::getVar('password');
      } else {
        $password = Misc::generatePassword();
      }
      $id = data_storage::getID($file, $password, $maxviews);
      if (is_bool($id[0]) && $id[0]) {
        $completeURL = 'https://tempfiles.carlgo11.com/download/' . $id[1] . '/?p=' . urlencode($password);
        $output['success'] = true;
        $output['url'] = $completeURL;
        if (Misc::getVar('password') == NULL) {
          $output['passowrd-mode'] = 'Server generated.';
        } else {
          $output['passowrd-mode'] = 'User generated.';
        }
        $output['passowrd'] = $password;
        if ($maxviews != NULL) {
          $output['maxviews'] = (int) $maxviews;
        }
        sendOutput($output);
      } else {
        $output['error'] = $id[1];
        sendOutput($output);
      }
    } else {
      $output['error'] = 'File size exceeded the limit of ' . $config['max-file-size'] . ".";
    }
  } else {
    $output['error'] = 'No file supplied.';
    sendOutput($output);
  }
}

function delete() {
  
}
