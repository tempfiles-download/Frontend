<?php
$content_dir = __DIR__ . '/res';
include_once 'res/init.php';
include_once 'res/content/header.php';

if(Misc::getVar('enc_file')){
  $id = data_storage::getID($_POST['enc_file'], $_POST['enc_metadata']);
}
$protocol="http";
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
  $protocol="https";
}
?>

<body>
  <?php include $content_dir . '/content/navbar.php'; ?>

  <div class="container main_container">

    <div class="description">
      <h1>Temp File Storage</h1>
      <p class="lead">Store files to share with your friends and family for <strong>24 hours</strong>.</p>
    </div>

    <div class="upload_file" id="upload_file">
      <div class="center">
        <h2>Upload File</h2>
        <?php if (isset($id) && $id[0]) {
          ?>
          <div id="upload_success">
            <h3 class="text-success">Success! <span class=" glyphicon glyphicon-ok"></span></h3>
            <div class="form-group has-success">
              <label class="control-label col-sm-2" for="id">Link</label>
              <div class="col-sm-10">
                <input class="form-control" type="text" value="<?php echo $protocol . "://" . $_SERVER['HTTP_HOST']."/download/" . $id[1] . "/?p=" . $_POST['upload-password']; ?>" readonly="" id="url"/>
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
        </div>
        <!--<form action="." enctype="multipart/form-data" method="POST" accept-charset="UTF-8" class="dropzone"><div class='form-group center'><button class="btn btn-lg btn-success upload-btn" type="submit" name="submit" id="submit">Upload File</button></div></form>-->
        <form class="form-horizontal center upload-form" id="upload-form">
          <div class='form-group'><input type="file" name="file" id="file" required=""/></div>
          <div class='form-group'><input class="form-control" type="password" name="upload-password" id="upload-password" placeholder="Password"/></div>
          <div class='form-group center'><button class="btn btn-lg btn-success upload-btn" type="button" name="upload-submit" id="upload-submit">Upload File</button></div>
        </form>
        <form action="" enctype="multipart/form-data" method="POST" accept-charset="UTF-8" id="data-form">
          <input type="text" id="enc_file" name="enc_file"/>
          <input type="text" id="enc_metadata" name="enc_metadata"/>
        </form>

      <?php } ?>
    </div> <!-- /upload_file -->

  </div> <!-- /container -->
  <?php include $content_dir . '/content/footer.php'; ?>
  <script src="res/js/autogenPass.js"></script>
</body>
</html>
