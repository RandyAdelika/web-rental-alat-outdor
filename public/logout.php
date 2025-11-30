<?php
require_once '../includes/auth.php';
logout_user();
header('Location: /project-web-s5/web-rental-outdor/public/index.php');
exit;
?>
