<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="res/css/bootstrap.min.css" rel="stylesheet">
        <link href="res/css/tempfiles.css" rel="stylesheet">
        <?php
        $https = filter_input(INPUT_POST, 'css');
        if (isset($https)) {
            echo "<link href=\"" . filter_input(INPUT_POST, 'css') . "\" rel=\"stylesheet\">\n";
        }
        ?>
        <script src="res/js/jquery.js"></script>
        <script src="res/js/bootstrap.min.js"></script>
        <title>TempFiles</title>
    </head>
