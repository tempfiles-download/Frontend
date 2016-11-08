<?php

header('Content-Type: application/json');
include_once './res/API.php';

function output($success, $output) {
  $d = [];
  $d['success'] = $success;
  $d[$output[0]] = $output[1];
  return json_encode($d, JSON_PRETTY_PRINT);
}

$url = explode('/', strtolower($_SERVER['REQUEST_URI']));
$success = false;
if (count($url)) {
  if ($url[2] == 'upload') {
    if ($_FILES['file'] != NULL) {
      $file = $_FILES['file'];
      if (Misc::getVar('password') != NULL) {
        $password = Misc::getVar('password');
        $id = data_storage::getID($file, $password);
        if (is_bool($id[0]) && $id[0]) {
          $url = 'https://tempfiles.carlgo11.com/download/' . $id[1] . '/?p=' . Misc::getVar('password');
          $success = true;
          print(output($success, ['url', $url . '.']));
        } else {
          print(output($success, ['error', $id[1]] . '.'));
        }
      } else {
        print(output($success, ['error', 'No password.']));
      }
    } else {
      print(output($success, ['error', 'No file.']));
    }
  }
} else {
  print(output($success, ['error', 'Incorrectly formatted URL.']));
}
