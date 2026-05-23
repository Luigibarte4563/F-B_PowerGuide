<?php

session_start();

require_once __DIR__ . '/../../../src/config/connection.php';
require_once __DIR__ . '/../../../src/config/app.php';

$conn = getConnection();

/* =========================
   AUTH CHECK
========================= */
if (!isset($_SESSION['user'])) {
    header("Location: " . FRONTEND_URL . "/src/auth/auth.php?page=login");
    exit();
}

/* =========================
   METHOD CHECK
========================= */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . FRONTEND_URL . "/src/dashboard/user/settings.php");
    exit();
}

/* =========================
   INPUT
========================= */
$name  = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');

if ($name === '' || $email === '') {
    header("Location: " . FRONTEND_URL . "/src/dashboard/user/settings.php?error=empty_fields");
    exit();
}

/* =========================
   IMAGE UPLOAD
========================= */

$picturePath = null;

if (isset($_FILES['picture']) && $_FILES['picture']['error'] === 0) {

    // ✅ FIXED: correct physical upload directory
    $uploadDir = __DIR__ . '/../../../backend/public/uploads/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // original filename
    $originalName = basename($_FILES["picture"]["name"]);

    // sanitize filename
    $safeName = preg_replace("/[^a-zA-Z0-9\._-]/", "", $originalName);

    // unique filename
    $fileName = time() . "_" . $safeName;

    $targetFile = $uploadDir . $fileName;

    // validate extension from ORIGINAL file
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($ext, $allowed)) {
        header("Location: " . FRONTEND_URL . "/src/dashboard/user/settings.php?error=invalid_image");
        exit();
    }

    // move file
    if (move_uploaded_file($_FILES["picture"]["tmp_name"], $targetFile)) {

        // ✅ FIXED PUBLIC URL (THIS MUST MATCH YOUR PROJECT FOLDER)
        $picturePath = "/PowerGuides/backend/public/uploads/" . $fileName;
    }
}

/* =========================
   UPDATE DATABASE
========================= */
$user_id = $_SESSION['user']['id'];

if ($picturePath) {

    $sql = "UPDATE users 
            SET name = ?, email = ?, picture = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$name, $email, $picturePath, $user_id]);

} else {

    $sql = "UPDATE users 
            SET name = ?, email = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$name, $email, $user_id]);
}

/* =========================
   UPDATE SESSION
========================= */
$_SESSION['user']['name'] = $name;
$_SESSION['user']['email'] = $email;

if ($picturePath) {
    $_SESSION['user']['picture'] = $picturePath;
}

/* =========================
   REDIRECT SUCCESS
========================= */
header("Location: " . FRONTEND_URL . "/src/dashboard/user/settings.php?success=1");
exit();