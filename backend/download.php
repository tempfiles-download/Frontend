<?php

use com\carlgo11\tempfiles\DataStorage;
use com\carlgo11\tempfiles\Misc;

include_once __DIR__ . '/src/com/carlgo11/tempfiles/Autoload.php';

/**
 * Only used to get legacy variables.
 * If the client uses the old link/variable method it will be redirected to the new one.
 *
 * @since 1.0
 * @since 2.2 Moved to own function.
 * @deprecated 2.0 Use `<PROTOCOL>://<DOMAIN>/DOWNLOAD/<ID>/?p=<PASSWORD>` instead.
 */
function getVariables() {
    if (Misc::getVar('f') !== NULL && Misc::getVar('p') !== NULL) {
        header('Location: /download/' . Misc::getVar('f') . '/?p=' . Misc::getVar('p'));
        exit;
    }
}

// Backwards compatibility
getVariables();


$url = explode('/', strtolower(filter_input(INPUT_SERVER, 'REQUEST_URI')));
$id = $url[2];
$p = Misc::getVar('p');
$file = DataStorage::getFile($id, $p);
if (isset($file)) {
    $metadata = $file->getMetaData();
    header('Content-Description: File Transfer');
    header('Content-Type: ' . base64_decode($metadata['type']));
    header('Content-Disposition: inline; filename="' . base64_decode($metadata['name']) . '"');
    header('Expires: 0');
    header('Pragma: public');
    header('Content-Length: ' . base64_decode($metadata['size']));
    echo($file->getContent());

    if ($file->setCurrentViews(($file->getCurrentViews() + 1)))
        DataStorage::setViews($file->getMaxViews(), ($file->getCurrentViews() + 1), $file, $p);
} else {
    header(filter_input(INPUT_SERVER, 'SERVER_PROTOCOL') . " 404 File Not Found");
    header('Location: /download-404');
}

exit;
