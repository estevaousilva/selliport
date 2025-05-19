<?php
session_start();
session_destroy();
header("Location:configuration.php");
exit;
?>
