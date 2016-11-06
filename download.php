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

if(Misc::getVar('f') != false && Misc::getVar('p') != false){
  $f=Misc::getVar('f');
  $p=Misc::getVar('p');
  session_start();
  session_unset();
  header('Location: https://tempfiles.carlgo11.com/download/'.$f.'/?p='.$p);
}else{


$url = explode('/', strtolower($_SERVER['REQUEST_URI']));
$e = data_storage::getFile($url[2], Misc::getVar("p")); # Returns [0] = File Meta Data, [1] = File Content.
if ($e[0] != NULL) {
  $metadata = explode(" ", $e[0]); # Returns [0] = File Name, [1] = File Length, [2] = File Type.
  $file_type = $metadata[2];
  if (!isset($_GET['raw'])) {
    if (getFormat($file_type, "image")) {
      echo '<img src="data:' . $file_type . ';base64,' . base64_encode($e[1]) . '"/>';
      exit;
    } elseif (getFormat($file_type, "audio")) {
      echo '<audio controls src="data:' . $file_type . ';base64,' . base64_encode($e[1]) . '"/>';
      exit;
    } elseif (getFormat($file_type, "video")) {
      echo '<video controls><source type="' . $file_type . '" src="data:' . $file_type . ';base64,' . base64_encode($e[1]) . '"></video>';
      exit;
    }
  }
  header('Content-Description: File Transfer');
  header('Content-Type: ' . $file_type);
  header('Content-Disposition: attachment; filename="' . $metadata[0] . '"');
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