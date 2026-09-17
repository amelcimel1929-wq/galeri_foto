<?php
session_start();

// Import koneksi database
include '../config/connection.php';

// Otomatis cari variabel koneksi
$db_connection = $conn ?? $koneksi ?? $db ?? $connect ?? null;

if (!$db_connection) {
    die("Error: Variabel koneksi database tidak ditemukan.");
}

$id_user = $_SESSION['id_user'] ?? null;

// Jika tidak ada user login atau bukan request POST
if (!$id_user || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $redirect = $_SERVER['HTTP_REFERER'] ?? '/galeri_foto/frontend/pages/profile.php';
    header("Location: $redirect");
    exit;
}

if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['foto_profil']['tmp_name'];
    $fileName = $_FILES['foto_profil']['name'];
    
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($fileExtension, $allowedExtensions)) {
        // Penamaan file unik
        $newFileName = 'avatar_' . $id_user . '_' . time() . '.' . $fileExtension;
        
        // Simpan langsung di folder uploads root
        $uploadFileDir = '../../uploads/';

        // Buat folder jika belum ada
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }

        $dest_path = $uploadFileDir . $newFileName;

        // Pindahkan file
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            // Update nama file di kolom foto_profil tabel user
            $query = "UPDATE user SET foto_profil = '$newFileName' WHERE id_user = '$id_user'";
            mysqli_query($db_connection, $query);

            // Update session
            $_SESSION['foto_profil'] = $newFileName;
        }
    }
}

// Redirect kembali ke halaman profile
$redirect = $_SERVER['HTTP_REFERER'] ?? '/galeri_foto/frontend/pages/profile.php';
header("Location: $redirect");
exit;