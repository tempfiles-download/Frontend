<?php
if ($conf['display-git-hash']) {
    $hashlong = file_get_contents('.git/refs/heads/master');
    $hashshort = substr($hashlong, 0, 7);
}
?>
<footer>
    <p>Made by <a href="https://carlgo11.com/">Carlgo11</a>. <?php if (!empty($hashshort)) { ?> Version: <a href="https://github.com/Carlgo11/tempfiles" title="<?php print($hashlong); ?>"><?php print($hashshort); ?></a><?php } ?></p>
</footer>
<script src="res/js/night-mode.js"></script>
