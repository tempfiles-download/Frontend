<?php

function getCURL(string $id, string $password) {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.tempfiles.carlgo11.com/download/?id={$id}&p={$password}",
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => [
            "cache-control: no-cache"
        ]
    ]);
    return $curl;
}

function return404() {
    header($_SERVER['SERVER_PROTOCOL'] . " 404 File Not Found");
    header('Location: /download-404');
    exit;
}

$url = explode('/', strtolower($_SERVER['REQUEST_URI']));
$id = filter_var($url[1]);
$password = filter_input(INPUT_GET, "p");

$curl = getCURL($id, $password);

// Execute cURL command and get response data
$response = json_decode(curl_exec($curl));

$error = curl_error($curl);
if (isset($error)) {
    error_log($error);
    return404();
}

if (isset($data)) {

    // Set headers
    header("Content-Description: File Transfer");
    header("Expires: 0");
    header("Pragma: public");
    header("Content-Type: {$response['type']}");
    header("Content-Disposition: inline; filename={$response['filename']}");
    header("Content-Length: {$response['length']}");

    // output file contents
    echo "{$response['data']}";

} else return404();
exit;
