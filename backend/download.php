<?php

include __DIR__ . '/res/init.php';

/**
 * Compare max views with current views.
 * @param int $currentviews Current views.
 * @param int $maxviews Maximum allowed views.
 * @param string $id ID.
 * @return boolean Returns true if current views surpass the maximum views. Otherwise returns false.
 */
function compareViews($currentviews, $maxviews, string $id) {
    if (isset($currentviews) && isset($maxviews)) {
        if (($currentviews + 1) >= $maxviews) {
            return DataStorage::deleteFile($id);
        } else {
            return DataStorage::setViews(intval($maxviews), ($currentviews + 1), $id);
        }
    }
    return false;
}

/**
 * Only used to get legacy variables.
 * If the client uses the old link/variable method it will be redirected to the new one.
 * @since 1.0
 * @since 2.2 Moved to own function.
 * @deprecated 2.0 Use `<PROTOCOL>://<DOMAIN>/DOWNLOAD/<ID>/?p=<PASSWORD>` instead.
 */
function getVariables() {
    if (isset(Misc::getVar('f')) && isset(Misc::getVar('p'))) {
        header('Location: /download/' . Misc::getVar('f') . '/?p=' . Misc::getVar('p'));
        die();
    }
}

// Backwards compatibility
getVariables();


$url = explode('/', strtolower(filter_input(INPUT_SERVER, 'REQUEST_URI')));
$id = $url[2];
$file = DataStorage::getFile($id, Misc::getVar('p'));
if (isset($file)) {
    $metadata = $file->getMetaData();
    header('Content-Description: File Transfer');
    header('Content-Type: ' . base64_decode($metadata['type']));
    header('Content-Disposition: inline; filename="' . base64_decode($metadata['name']) . '"');
    header('Expires: 0');
    header('Pragma: public');
    header('Content-Length: ' . base64_decode($metadata['size']));
    echo($file->getContent());

    compareViews($file->getCurrentViews(), $file->getMaxViews(), $file->getID());
    exit;
} else {
    $css = filter_input(INPUT_POST, 'css');
    header(filter_input(INPUT_SERVER, 'SERVER_PROTOCOL') . " 404 File Not Found");
    if (!isset(Misc::getVar("raw"))) {
        header('Location: /download-404');
    }
    exit;
}
