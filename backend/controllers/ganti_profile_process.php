<?php
session_start();

require_once __DIR__ . '/../config/connection.php';
// connection.php diasumsikan mendefinisikan $koneksi (mysqli), sama seperti di foto_process.php & notifikasi.php

$id_user = $_SESSION['id_user'] ?? null;

if (!$id_user || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $redirect = $_SERVER['HTTP_REFERER'] ?? '/galeri_foto/frontend/pages/profile.php';
    header("Location: $redirect");
    exit;
}

if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['foto_profil']['tmp_name'];
    $fileName    = $_FILES['foto_profil']['name'];

    $fileExtension     = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($fileExtension, $allowedExtensions)) {
        // Penamaan file unik
        $newFileName = 'avatar_' . $id_user . '_' . time() . '.' . $fileExtension;

        // FIX: naik 1 folder pakai __DIR__ -> menuju backend/uploads/
        // (sebelumnya '../../uploads/' salah nyasar ke folder root, beda dari foto_process.php)
        $uploadFileDir = __DIR__ . '/../uploads/';

        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }

        $dest_path = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            // FIX: pakai prepared statement, bukan string interpolation langsung (rawan SQL injection)
            $query = "UPDATE user SET foto_profil = ? WHERE id_user = ?";
            $stmt  = $koneksi->prepare($query);
            $stmt->bind_param("si", $newFileName, $id_user);
            $stmt->execute();
            $stmt->close();

            // Update session
            $_SESSION['foto_profil'] = $newFileName;
        }
    }
}

$redirect = $_SERVER['HTTP_REFERER'] ?? '/galeri_foto/frontend/pages/profile.php';
header("Location: $redirect");
exit;