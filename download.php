<?php

include __DIR__ . '/res/init.php';

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

/** Backwards compatibility.
 * If the client uses the old link method it will be redirected to the new one.
 */
if (Misc::getVar('f') != false && Misc::getVar('p') != false) {
    $f = Misc::getVar('f');
    $p = Misc::getVar('p');
    header('Location: https://tempfiles.carlgo11.com/download/' . $f . '/?p=' . $p);
} else {

    $url = explode('/', strtolower(filter_input(INPUT_SERVER, 'REQUEST_URI')));
    $e = DataStorage::getFile($url[2], Misc::getVar("p")); # Returns [0] = File Meta Data, [1] = File Content.
    if ($e[0] != NULL) {
        $metadata = explode(" ", $e[0]); # Returns [0] = File Name, [1] = File Length, [2] = File Type, [3] = Deletion Password.
        header('Content-Description: File Transfer');
        header('Content-Type: ' . base64_decode($metadata[2]));
        header('Content-Disposition: inline; filename="' . base64_decode($metadata[0]) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . base64_decode($metadata[1]));
        echo($e[1]);
        $viewsArray = $e[2];
        if (is_array($viewsArray)) {
            compareViews($viewsArray[0], $viewsArray[1], $url[2]);
        }
        exit;
        
    } else {
        
        $css = filter_input(INPUT_POST, 'css');
        header(filter_input(INPUT_SERVER, 'SERVER_PROTOCOL') . " 404 File Not Found");
        if (Misc::getVar("raw") == NULL) {
            $css = "/res/css/download_404.css";
            include 'res/content/header.php';
            include 'res/content/navbar.php';
            include 'res/content/download_404.php';
        }
        exit;
    }
}
