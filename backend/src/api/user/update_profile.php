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

    // ✅ Correct physical uploads folder
    $uploadDir = __DIR__ . '/../../../public/uploads/';

    // ✅ Create uploads folder if missing
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // ✅ Original filename
    $originalName = basename($_FILES['picture']['name']);

    // ✅ Sanitize filename
    $safeName = preg_replace("/[^a-zA-Z0-9._-]/", "", $originalName);

    // ✅ Unique filename
    $fileName = time() . "_" . $safeName;

    // ✅ Final physical file path
    $targetFile = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $fileName;

    // ✅ Validate extension
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($ext, $allowed)) {
        header("Location: " . FRONTEND_URL . "/src/dashboard/user/settings.php?error=invalid_image");
        exit();
    }

    // ✅ Upload file
    if (move_uploaded_file($_FILES['picture']['tmp_name'], $targetFile)) {

        // ✅ Correct browser-accessible path
        $picturePath = "/PowerGuides/backend/public/uploads/" . $fileName;

    } else {

        header("Location: " . FRONTEND_URL . "/src/dashboard/user/settings.php?error=upload_failed");
        exit();
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
?>