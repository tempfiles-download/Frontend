<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="res/css/bootstrap.min.css" rel="stylesheet">
        <link href="res/css/tempfiles.css" rel="stylesheet">
        <?php
        if (isset($_POST['css'])) {
            echo "<link href=\"" . $_POST['css'] . "\" rel=\"stylesheet\">\n";
        }
        ?>
        <script src="res/js/bootstrap.min.js"></script>
        <title><?php include __DIR__ . '/../config.php';
        echo $conf['title'];
        ?></title>
    </head>
