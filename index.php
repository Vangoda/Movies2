<?php //Redirects to real index.php
$_SESSION["requestFrom"]="root";
header("Location: pages/index.php");
die();
?>