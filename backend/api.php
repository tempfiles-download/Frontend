<?php

namespace com\carlgo11\tempfiles\api;
// Load resources

require_once __DIR__ . '/src/com/carlgo11/tempfiles/Autoload.php';

$url = explode('/', strtolower(filter_input(INPUT_SERVER, 'REQUEST_URI')));


try {
    switch ($url[2]) {
        case 'cleanup':
            require_once __DIR__ . '/src/com/carlgo11/tempfiles/api/Cleanup.php';
            new Cleanup();
            break;
        case 'delete':
            require_once __DIR__ . '/src/com/carlgo11/tempfiles/api/Delete.php';
            new Delete();
            break;
        case 'download':
            require_once __DIR__ . '/src/com/carlgo11/tempfiles/api/Download.php';
            new Download();
            break;
        case 'upload':
            require_once __DIR__ . '/src/com/carlgo11/tempfiles/api/Upload.php';
            new Upload();
            break;
        default:
            throw new Exception('Unknown or missing function.');
            break;
    }
} catch (Exception $ex) {
    error_log($ex); //Spews out the error to log. Maybe not so good for production env?
    $api = new API();
    $api->addMessage('error', $ex->getMessage());
    $api->outputJSON(500);
}