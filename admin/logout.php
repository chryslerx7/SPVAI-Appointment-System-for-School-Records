<?php
require_once('../class/Auth.php');
$auth->logout();
header('Location: ../index.php');
exit();
?>