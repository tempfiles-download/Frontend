<?php

include __DIR__ . '/res/init.php';

function getFormat($type, $format) {
  if (strpos($type, $format) !== false) {
    return true;
  }
}

function compareViews($currentviews, $maxviews, $id) {
  if (($currentviews + 1) >= $maxviews) {
    return data_storage::deleteFile($id);
  } else {
    return data_storage::setViews(intval($maxviews), ($currentviews + 1), $id);
  }
  return false;
}

/** Backwards compatibility.
 * If the client uses the old link method it will be redirected to the new one. 
 */
if (Misc::getVar('f') != false && Misc::getVar('p') != false) {
  $f = Misc::getVar('f');
  $p = Misc::getVar('p');
  header('Location: https://tempfiles.carlgo11.com/download/' . $f . '/?p=' . $p);
} else {

  $url = explode('/', strtolower($_SERVER['REQUEST_URI']));
  $e = data_storage::getFile($url[2], Misc::getVar("p")); # Returns [0] = File Meta Data, [1] = File Content.

  if ($e[0] != NULL && sizeof($e[0]) == 3) {
    $metadata = explode(" ", $e[0]); # Returns [0] = File Name, [1] = File Length, [2] = File Type.
    header('Content-Description: File Transfer');
    header('Content-Disposition: inline; filename="' . $metadata[0] . '"');
    header('Content-Type: ' . $metadata[2]);
    header('Content-Length: ' . $metadata[1]);
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    echo($e[1]);
    $viewsArray = $e[2];
    if (is_array($viewsArray)) {
      compareViews($viewsArray[0], $viewsArray[1], $url[2]);
    }
    exit;
  } else {
    header($_SERVER["SERVER_PROTOCOL"] . " 404 File Not Found");
    if (Misc::getVar("raw") == NULL) {
      $_POST['css'] = "/res/css/download_404.css";
      include 'res/content/header.php';
      include 'res/content/navbar.php';
      include 'res/content/download_404.php';
    }
    exit;
  }
}