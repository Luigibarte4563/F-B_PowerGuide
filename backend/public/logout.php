<?php
require_once __DIR__ . '/../src/config/app.php';

session_start();

$_SESSION = [];
session_unset();
session_destroy();

setcookie("jwt_token", "", time() - 3600, "/", "", false, true);

// header("Location: index.php");
header("Location: " . FRONTEND_URL . "/src/index.php");
exit;

?>