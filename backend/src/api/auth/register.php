<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../../src/config/connection.php';
require_once __DIR__ . '/../../../src/config/app.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../src/config/env.php';

use Firebase\JWT\JWT;

$conn = getConnection();
$secret_key = $_ENV['JWT_SECRET_KEY'];

/* =========================
   ONLY POST ALLOWED
========================= */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . BACKEND_URL . "/public/auth/auth.php?page=register");
    exit;
}

/* =========================
   GET DATA
========================= */
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$passwordRaw = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

/* =========================
   CLEAR OLD SESSION MESSAGES
========================= */
unset($_SESSION['register_error'], $_SESSION['success']);

/* =========================
   VALIDATION
========================= */
if (!$name || !$email || !$passwordRaw || !$confirmPassword) {
    $_SESSION['register_error'] = "All fields are required.";
    redirect();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['register_error'] = "Invalid email format.";
    redirect();
}

if ($passwordRaw !== $confirmPassword) {
    $_SESSION['register_error'] = "Passwords do not match.";
    redirect();
}

if (strlen($passwordRaw) < 6) {
    $_SESSION['register_error'] = "Password must be at least 6 characters.";
    redirect();
}

/* =========================
   CHECK EMAIL EXISTS
========================= */
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);

if ($stmt->fetch()) {
    $_SESSION['register_error'] = "Email already exists.";
    redirect();
}

/* =========================
   CREATE USER
========================= */
$password = password_hash($passwordRaw, PASSWORD_BCRYPT);

$stmt = $conn->prepare("
    INSERT INTO users (name, email, password, auth_provider, role, created_at)
    VALUES (?, ?, ?, 'local', 'user', NOW())
");

if (!$stmt->execute([$name, $email, $password])) {
    $_SESSION['register_error'] = "Registration failed.";
    redirect();
}

$userId = $conn->lastInsertId();

/* =========================
   FETCH USER
========================= */
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['register_error'] = "User retrieval failed.";
    redirect();
}

/* =========================
   JWT
========================= */
$payload = [
    "id" => $user['id'],
    "email" => $user['email'],
    "role" => $user['role'],
    "auth_provider" => $user['auth_provider'],
    "iat" => time(),
    "exp" => time() + 3600,
    "type" => "access"
];

$jwt = JWT::encode($payload, $secret_key, 'HS256');

/* =========================
   COOKIE
========================= */
setcookie("jwt_token", $jwt, [
    "expires" => time() + 3600,
    "path" => "/",
    "httponly" => true,
    "samesite" => "Lax",
    "secure" => false
]);

/* =========================
   SESSION USER
========================= */
$_SESSION['user'] = [
    "id" => $user['id'],
    "name" => $user['name'],
    "email" => $user['email'],
    "role" => $user['role'],
    "auth_provider" => $user['auth_provider']
];

/* =========================
   SUCCESS MESSAGE
========================= */
$_SESSION['success'] = "Registration successful.";

/* =========================
   REDIRECT FUNCTION
========================= */
function redirect()
{
    header("Location: " . BACKEND_URL . "/public/auth/auth.php?page=register");
    exit;
}

/* =========================
   FINAL REDIRECT (AUTO LOGIN FLOW)
========================= */
if ($user['role'] === "electric_company") {
    header("Location: " . BACKEND_URL . "/public/dashboard/electric/dashboard.php");
} else {
    header("Location: " . FRONTEND_URL . "/src/dashboard/user/dashboard.php");
}
exit;