<?php

namespace com\carlgo11\tempfiles\api;
// Load resources

use Exception;

require_once __DIR__ . '/src/com/carlgo11/tempfiles/Autoload.php';

$url = explode('/', strtolower($_SERVER['REQUEST_URI']));
$method = filter_var($_SERVER['REQUEST_METHOD'], FILTER_SANITIZE_STRING);

if (!isset($method)) throw new Exception("HTTP method not set.");

try {
    switch ($url[1]) {
        case 'cleanup':
            require_once __DIR__ . '/src/com/carlgo11/tempfiles/api/Cleanup.php';
            new Cleanup($method);
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
//    error_log($ex); //Spews out the error to log. Maybe not so good for production env?
    $api = new API();
    $api->addMessage('error', $ex->getMessage());
    $api->outputJSON(500);
}