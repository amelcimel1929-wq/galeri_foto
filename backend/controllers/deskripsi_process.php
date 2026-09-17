<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Koneksi dari backend/controllers/ ke backend/config/connection.php
include '../config/connection.php';

$id_user = $_SESSION['id_user'] ?? null;

if (!$id_user) {
    header('Location: ../../frontend/pages/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = $koneksi ?? $conn;

    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $bio          = trim($_POST['bio'] ?? '');
    $username     = trim($_POST['username'] ?? '');

    // Upload Foto Profil
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath   = $_FILES['foto_profil']['tmp_name'];
        $fileName      = $_FILES['foto_profil']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName   = 'profile_' . $id_user . '_' . time() . '.' . $fileExtension;
            $uploadFileDir = '../../uploads/';

            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }

            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $stmtFoto = $db->prepare("UPDATE user SET foto_profil = ? WHERE id_user = ?");
                $stmtFoto->bind_param("si", $newFileName, $id_user);
                $stmtFoto->execute();
                $stmtFoto->close();

                $_SESSION['foto_profil'] = $newFileName;
            }
        }
    }

    // Update data profil
    $stmt = $db->prepare("UPDATE user SET nama_lengkap = ?, bio = ?, username = ? WHERE id_user = ?");
    $stmt->bind_param("sssi", $nama_lengkap, $bio, $username, $id_user);

    if ($stmt->execute()) {
        $_SESSION['username'] = $username;
        // Redirect balik dari backend/controllers/ ke frontend/pages/profile_pribadi.php
        header('Location: ../../frontend/pages/profile_pribadi.php?status=success');
    } else {
        header('Location: ../../frontend/pages/deskripsi.php?status=error');
    }

    $stmt->close();
    exit;
} else {
    header('Location: ../../frontend/pages/deskripsi.php');
    exit;
}