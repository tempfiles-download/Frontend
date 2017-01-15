<?php

function getFormat($type, $format) {
  if (strpos($type, $format) !== false) {
    return true;
  }
}

include __DIR__ . '/res/API.php';

/** Backwards compatibility.
 * If the client uses the old link method it will be redirected to the new one. 
 */
if (Misc::getVar('f') != false && Misc::getVar('p') != false) {
  $f = Misc::getVar('f');
  $p = Misc::getVar('p');
  session_start();
  session_unset();
  header('Location: https://tempfiles.carlgo11.com/download/' . $f . '/?p=' . $p);
} else {


  $url = explode('/', strtolower($_SERVER['REQUEST_URI']));
  $e = data_storage::getFile($url[2], Misc::getVar("p")); # Returns [0] = File Meta Data, [1] = File Content.
  if ($e[0] != NULL) {
    $metadata = explode(" ", $e[0]); # Returns [0] = File Name, [1] = File Length, [2] = File Type.
    $file_type = $metadata[2];
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $file_type);
    header('Content-Disposition: inline; filename="' . $metadata[0] . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . $metadata[1]);
    echo($e[1]);
    exit;
  } else {
    header($_SERVER["SERVER_PROTOCOL"] . " 404 File Not Found");
    if (Misc::getVar("raw") != NULL) {
      $_POST['css'] = "res/css/download_404.css";
      include '/res/content/header.php';
      include '/res/content/navbar.php';
      include '/res/content/download_404.php';
    }
    exit;
  }
}