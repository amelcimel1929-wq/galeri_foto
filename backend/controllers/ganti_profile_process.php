<?php
session_start();

require_once __DIR__ . '/../config/connection.php';
// connection.php mendefinisikan $koneksi (mysqli)

$id_user = $_SESSION['id_user'] ?? null;

if (!$id_user || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $redirect = $_SERVER['HTTP_REFERER'] ?? '/galeri_foto/frontend/pages/profile.php';
    header("Location: $redirect");
    exit;
}

if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['foto_profil']['tmp_name'];
    $fileName    = $_FILES['foto_profil']['name'];

    // 1. Validasi Ekstensi File
    $fileExtension     = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    // 2. Validasi MIME Type Asli File
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $fileTmpPath);
    finfo_close($finfo);

    $allowedMime = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];

    // Pengecekan Ganda: Ekstensi DAN MIME Type Harus Valid
    if (in_array($fileExtension, $allowedExtensions) && in_array($mimeType, $allowedMime)) {
        
        // Penamaan file unik
        $newFileName = 'avatar_' . $id_user . '_' . time() . '.' . $fileExtension;

        // Path folder upload (backend/uploads/)
        $uploadFileDir = __DIR__ . '/../uploads/';

        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }

        $dest_path = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            // Prepared statement untuk keamanan SQL Injection
            $query = "UPDATE user SET foto_profil = ? WHERE id_user = ?";
            $stmt  = $koneksi->prepare($query);
            $stmt->bind_param("si", $newFileName, $id_user);
            $stmt->execute();
            $stmt->close();

            // Update session
            $_SESSION['foto_profil'] = $newFileName;
        }
    } else {
        echo "File Tidak Valid";
    }
}

$redirect = $_SERVER['HTTP_REFERER'] ?? '/galeri_foto/frontend/pages/profile.php';
header("Location: $redirect");
exit;