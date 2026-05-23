<?php

session_start();

require_once __DIR__ . '/../../../src/config/connection.php';
require_once __DIR__ . '/../../../src/config/app.php';

$conn = getConnection();

/* =========================
   AUTH CHECK
========================= */
if (!isset($_SESSION['user'])) {
    header("Location: " . FRONTEND_URL . "/auth/auth.php?page=login");
    exit();
}

$user_id = $_SESSION['user']['id'];

/* =========================
   INPUT
========================= */
$current = trim($_POST['current_password'] ?? '');
$new     = trim($_POST['new_password'] ?? '');
$confirm = trim($_POST['confirm_password'] ?? '');

/* =========================
   VALIDATION
========================= */
if ($current === '' || $new === '' || $confirm === '') {
    header("Location: " . FRONTEND_URL . "/src/dashboard/user/settings.php?error=empty_fields");
    exit();
}

if ($new !== $confirm) {
    header("Location: " . FRONTEND_URL . "/src/dashboard/user/settings.php?error=password_mismatch");
    exit();
}

/* =========================
   GET USER
========================= */
$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: " . FRONTEND_URL . "/src/dashboard/user/settings.php?error=user_not_found");
    exit();
}

/* =========================
   VERIFY PASSWORD
========================= */
if (!password_verify($current, $user['password'])) {
    header("Location: " . FRONTEND_URL . "/src/dashboard/user/settings.php?error=wrong_password");
    exit();
}

/* =========================
   UPDATE PASSWORD
========================= */
$newHashed = password_hash($new, PASSWORD_BCRYPT);

$stmt = $conn->prepare("
    UPDATE users 
    SET password = ?
    WHERE id = ?
");

$stmt->execute([$newHashed, $user_id]);

/* =========================
   SUCCESS
========================= */
header("Location: " . FRONTEND_URL . "/src/dashboard/user/settings.php?success=password_updated");
exit();