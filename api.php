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
if ($url[2] == 'upload') {
  if ($_FILES['file'] != NULL) {
    if (Misc::getVar('password') != NULL) {
      $file = $_FILES['file'];
      $password = Misc::getVar('password');
      $id = data_storage::getID($file, $password);
      if (is_bool($id[0]) && $id[0]) {
        $url = 'https://tempfiles.carlgo11.com/download/' . $id[1] . '/?p=' . Misc::getVar('password');
        $success=true;
        print(output($success, ['url', $url]));
      }else{
        print(output($success, ['error', $id[1]]));
      }
    } else {
      print(output($success, ['error', 'no password']));
    }
  } else {
    print(output($success, ['error', 'no file']));
  }
}