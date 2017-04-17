<?php
if ($conf['display-git-hash']) {
  $hash = shell_exec('git rev-parse --short HEAD');
}
?>
<footer>
  <p>Made by <a href="https://carlgo11.com/">Carlgo11</a>. <?php if (!empty($hash)) { ?> Version: <a href="https://github.com/Carlgo11/tempfiles"><?php print($hash); ?></a><?php } ?></p>
</footer>