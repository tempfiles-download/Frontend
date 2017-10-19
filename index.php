<?php
$content_dir = __DIR__ . '/res';
include_once 'res/init.php';
include_once 'res/content/header.php';

if (Misc::getVar('upload-password') != NULL) {
  $id = data_storage::getID($_FILES['upload-file'], Misc::getVar('upload-password'));
}

$protocol = "http";
if (isset(filter_input(INPUT_SERVER, 'HTTPS')) && filter_input(INPUT_SERVER, 'HTTPS') != 'off') {
  $protocol = "https";
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
                <input class="form-control" type="text" value="<?php echo $protocol . "://" . filter_input(INPUT_SERVER, 'HTTP_HOST') . "/download/" . $id[1] . "/?p=" . filter_input(INPUT_POST, 'upload-password'); ?>" readonly="" id="url" onClick="this.select();">
              </div>
            </div>
            <div class="form-group has-success">
              <label class="control-label col-sm-2" for="id">ID</label>
              <div class="col-sm-10">
                <input class="form-control" type="text" value="<?php echo $id[1]; ?>" readonly="" style="width: auto" onClick="this.select();">
              </div>
            </div>
          </div>
          <?php
        } else {
          if (isset($id) && !$id[0]) {
            ?><div class="upload_failed"><div class="alert alert-danger"><h3>Upload Failed</h3><p>Error: <?php echo $id[1]; ?></p></div></div><?php } ?>
        </div>
        <form class="form-horizontal center upload-form" action="" enctype="multipart/form-data" method="POST" accept-charset="UTF-8" id="upload-form">
          <div class='form-group'><input type="file" name="upload-file" id="file" required=""/></div>
          <div class='form-group'><input class="form-control" type="password" name="upload-password" id="upload-password" placeholder="Password"/></div>
          <div class='form-group center'><button class="btn btn-lg btn-success upload-btn" type="button" name="upload-submit" id="upload-submit">Upload File</button></div>
        </form>

      <?php } ?>
    </div> <!-- /upload_file -->

  </div> <!-- /container -->
  <?php include $content_dir . '/content/footer.php'; ?>
  <script src="res/js/autogenPass.js"></script>
</body>
</html>
