<?php

function getVar($name){
    if (isset($_GET[$name])) return $_GET[$name];
    if (isset($_POST[$name])) return $_POST[$name];
}

include __DIR__ . '/res/API.php';
$e = data_storage::getFile(getVar("f"), getVar("p")); # Returns [0] = File Meta Data, [1] = File Content.
$metadata = explode(" ", $e[0]); # Returns [0] = File Name, [1] = File Length, [2] = File Type.
if (strpos($metadata[2], "image") != false) {
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $metadata[2]);
    header('Content-Disposition: attachment; filename="' . $metadata[0] . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . $metadata[1]);
    echo($e[1]);
    exit;
} else {
    echo '<img src="data:image/jpeg;base64,' . base64_encode($e[1]) . '"/>';
    exit;
}
