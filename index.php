<?php
$content_dir = __DIR__ . '/res';
include $content_dir . '/API.php';
include_once $content_dir . '/header.php';

$id = data_storage::checkUpload();
?>
<body>
    <nav class="navbar navbar-inverse navbar-fixed-top">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-controls="navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#" style="color: #eee;">TempFiles</a><span class="glyphicon glyphicon-floppy-save logo" aria-hidden="true"></span>
            </div>
            <div id="navbar" class="collapse navbar-collapse">
            </div><!--/.nav-collapse -->
        </div>
    </nav>

    <div class="container main_container">

        <div class="description">
            <h1>Temp File Storage</h1>
            <p class="lead">Store files to share with your friends and family for <strong>24 hours</strong>.</p>
        </div>

        <div class="upload_file">
            <h2>Upload File</h2>
            <?php if (isset($id) && $id[0]) {
                ?>
                <div id="upload_success">
                    <h3 class="text-success">Success! <span class=" glyphicon glyphicon-ok" ></span></h3>
                    <div class="form-group has-success">
                        <label class="control-label col-sm-2" for="id">Link</label>
                        <div class="col-sm-10">
                            <input class="form-control" type="text" value="<?php echo "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]download.php?f=" . $id[1] . "&p=" . $_POST['upload-password']; ?>" readonly="" id="url"/>
                        </div>
                    </div>
                    <div class="form-group has-success">
                        <label class="control-label col-sm-2" for="id">ID</label>
                        <div class="col-sm-10">
                            <input class="form-control" type="text" value="<?php echo $id[1]; ?>" readonly="" style="width: auto">
                        </div>
                    </div>
                </div>
                <?php
            } else {
                if (isset($id) && !$id[0]) {
                    ?><div class="upload_failed"><div class="alert alert-danger"><h3>Upload Failed</h3><p>Error: <?php echo $id[1]; ?></p></div></div><?php } ?>
                <form class="form-horizontal" action="#" enctype="multipart/form-data" method="POST" accept-charset="UTF-8" >
                    <div class='form-group'><input type="file" name="uploadedFile" id="uploadedFile" required=""/></div>
                    <div class='form-group'><input class="form-control" type="password" name="upload-password" id="upload-password" required=""  placeholder="Password"/></div>
                    <button class="btn btn-lg btn-success" type="submit" name="upload-submit" id="upload-submit" style="padding: 10px" >Upload File</button>
                </form>
            <?php } ?>
        </div>

        <div class="download_file">
            <h2>Download File</h2>
            <form class="form-horizontal" action="download.php" method="POST" accept-charset="UTF-8" target="_blank" >
                <div class='form-group'><input class="form-control" type="text" name="f" id="f" required="" placeholder="ID"/></div>
                <div class='form-group'><input class="form-control" type="password" name="p" id="p" required=""  placeholder="Password"/></div>
                <button class="btn btn-lg btn-success" type="submit">Download File</button>
            </form>
        </div>
<p>
<a href="http://jigsaw.w3.org/css-validator/check/referer">
    <img style="border:0;width:88px;height:31px"
        src="http://jigsaw.w3.org/css-validator/images/vcss-blue"
        alt="Valid CSS!" />
    </a>
</p>
        
    </div><!-- /.container -->

</body>
</html>
