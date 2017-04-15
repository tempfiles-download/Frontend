<?php 
  $hash = shell_exec('git rev-parse --short HEAD');
?>
<footer>
  <p>Made by <a href="https://carlgo11.com/">Carlgo11</a>. <?php if(!empty($hash)){ ?> Version: <?php print($hash); } ?></p>
</footer>