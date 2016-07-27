<div class="upload_file">
    <h2>Upload File</h2>
    <?php if (isset($id) && $id[0]) {
        ?>
        <div id="upload_success">
            <h3 class="text-success">Success! <span class=" glyphicon glyphicon-ok"></span></h3>
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
        <form class="form-horizontal" action="#" enctype="multipart/form-data" method="POST" accept-charset="UTF-8">
            <div class='form-group'><input type="file" name="uploadedFile" id="uploadedFile" required=""/></div>
            <div class='form-group'><input class="form-control" type="password" name="upload-password" id="upload-password" required=""  placeholder="Password"/></div>
            <button class="btn btn-lg btn-success" type="submit" name="upload-submit" id="upload-submit" style="padding: 10px">Upload File</button>
        </form>
    <?php } ?>
</div>