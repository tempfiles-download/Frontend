<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="/res/css/bootstrap.min.css" rel="stylesheet">
        <link href="/res/css/tempfiles.css" rel="stylesheet" title="bright">
        <link href="/res/css/tempfiles-dark.css" rel="alternate stylesheet" title="dark">
        <?php
        if (isset($_POST['css'])) {
            echo "<link href=\"" . $_POST['css'] . "\" rel=\"stylesheet\">\n";
        }
        ?>
        <script src="/res/js/jquery.js"></script>
        <script src="/res/js/bootstrap.min.js"></script>
        <script src="/res/js/night-mode.js"></script>
        <title>TempFiles</title>
    </head>
    <body>
        <div class="container">

            <form class="form-download"  action="" method="POST" accept-charset="UTF-8">
                <div class="text-danger">
                    <h2 class="form-download-heading text-danger">Try Again</h2>
                    <p><b>The ID or Password you entered does not match our database</b></p>
                    <p>Please try again.</p>
                </div>
                <input type="text" id="f" name="f" class="form-control" placeholder="ID" required autofocus>
                <input type="password" id="p" name="p" class="form-control" placeholder="Password" required>
                <button class="btn btn-lg btn-success btn-block" type="submit">Download</button>
            </form>

        </div>
    </body>
</html>
