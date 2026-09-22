<?php
session_start();
// Sesuaikan path ke file koneksi database Anda
include '../config/koneksi.php'; 

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../frontend/pages/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. PROSES UPLOAD FOTO PROFIL
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath   = $_FILES['foto_profil']['tmp_name'];
        $fileName      = $_FILES['foto_profil']['name'];

        // A. Cek Ekstensi File
        $fileExtension     = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        // B. Cek MIME Type Asli File (Pakai finfo)
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fileTmpPath);
        finfo_close($finfo);

        $allowedMime = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        // Jalankan upload HANYA jika ekstensi DAN MIME Type keduanya valid
        if (in_array($fileExtension, $allowedExtensions) && in_array($mimeType, $allowedMime)) {

            // Buat nama file unik
            $newFileName = 'profile_' . $user_id . '_' . time() . '.' . $fileExtension;
            
            // Path penyimpanan ke folder uploads
            $uploadFileDir = '../uploads/';

            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // Update nama foto di database
                $stmt = $koneksi->prepare("UPDATE users SET foto_profil = ? WHERE id = ?");
                $stmt->bind_param("si", $newFileName, $user_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    // 2. PROSES UPDATE BIO / DESKRIPSI
    if (isset($_POST['update_bio'])) {
        $bio = trim($_POST['bio']);
        
        $stmt = $koneksi->prepare("UPDATE users SET bio = ? WHERE id = ?");
        $stmt->bind_param("si", $bio, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    // Redirect kembali ke halaman profile di frontend
    header("Location: ../../frontend/pages/profile.php");
    exit();
}