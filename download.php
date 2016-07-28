<?php

function getVar($name) {
    if (isset($_GET[$name]))
        return $_GET[$name];
    if (isset($_POST[$name]))
        return $_POST[$name];
}

function getFormat($type, $format) {
    if (strpos($type, $format) !== false) {
        return true;
    }
}

include __DIR__ . '/res/API.php';
$e = data_storage::getFile(getVar("f"), getVar("p")); # Returns [0] = File Meta Data, [1] = File Content.
$metadata = explode(" ", $e[0]); # Returns [0] = File Name, [1] = File Length, [2] = File Type.
$file_type = $metadata[2];
if (getFormat($file_type, "image")) {
    echo '<img src="data:' . $file_type . ';base64,' . base64_encode($e[1]) . '"/>';
    exit;
} elseif (getFormat($file_type, "audio")) {
    echo '<audio controls src="data:' . $file_type . ';base64,' . base64_encode($e[1]) . '"/>';
    exit;
} elseif (getFormat($file_type, "video")) {
    echo '<video controls><source type="' . $file_type . '" src="data:' . $file_type . ';base64,' . base64_encode($e[1]) . '"></video>';
} else {
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $file_type);
    header('Content-Disposition: attachment; filename="' . $metadata[0] . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . $metadata[1]);
    echo($e[1]);
    exit;
}
