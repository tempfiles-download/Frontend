<?php
$content_dir = __DIR__ . '/res';
include 'res/init.php';
include_once 'res/content/header.php';
if (Misc::getVar('upload-password') != NULL) {
  $id = data_storage::getID($_FILES['upload-file'], Misc::getVar('upload-password'));
}
?>

<body>
  <?php include $content_dir . '/content/navbar.php'; ?>

  <div class="container main_container">

    <div class="description">
      <h1>Temp File Storage</h1>
      <p class="lead">Store files to share with your friends and family for <strong>24 hours</strong>.</p>
    </div>

    <?php include $content_dir . '/content/upload_file.php'; ?>

  </div><!-- /.container -->
  <?php include $content_dir . '/content/footer.php'; ?>
</body>
</html>
