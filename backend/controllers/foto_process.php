<?php
session_start();

require_once __DIR__ . '/../config/connection.php';

if (!isset($_SESSION['id_user'])) {
    header('Location: ../../frontend/pages/login.php');
    exit;
}

$id_user = $_SESSION['id_user'];
$action  = $_POST['action'] ?? $_GET['action'] ?? '';

// =========================
// UPLOAD FOTO (MYSQLI)
// =========================
if ($action === 'upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $judul_foto     = trim($_POST['judul_foto'] ?? '');
    $deskripsi_foto = trim($_POST['deskripsi_foto'] ?? '');
    $file           = $_FILES['foto'] ?? null;

    if (empty($judul_foto) || !$file || empty($file['name'])) {
        header('Location: ../../frontend/pages/tambah_foto.php?error=' . urlencode('Judul dan gambar wajib diisi'));
        exit;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        header('Location: ../../frontend/pages/tambah_foto.php?error=' . urlencode('Gagal membaca file gambar'));
        exit;
    }

    $ekstensi_diizinkan = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $ekstensi           = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ekstensi, $ekstensi_diizinkan)) {
        header('Location: ../../frontend/pages/tambah_foto.php?error=' . urlencode('Format gambar tidak didukung'));
        exit;
    }

    // Folder tujuan upload
    $folder_upload = __DIR__ . '/../uploads/';

    if (!is_dir($folder_upload)) {
        mkdir($folder_upload, 0777, true);
    }

    $nama_file = 'foto_' . uniqid() . '_' . time() . '.' . $ekstensi;
    $tujuan    = $folder_upload . $nama_file;

    if (move_uploaded_file($file['tmp_name'], $tujuan)) {

        // PERBAIKAN: Gunakan tanggal_ungahan (sesuai ERD DB) dan NOW() untuk mengisi tanggal otomatis
        $query = "INSERT INTO foto (id_user, judul_foto, deskripsi_foto, tanggal_ungahan, lokasi_file) VALUES (?, ?, ?, NOW(), ?)";
        
        $stmt = $koneksi->prepare($query);
        
        if ($stmt) {
            // "isss" -> i = integer (id_user), s = string (judul, deskripsi, lokasi_file)
            $stmt->bind_param("isss", $id_user, $judul_foto, $deskripsi_foto, $nama_file);
            $stmt->execute();
            $stmt->close();

            header('Location: ../../index.php');
            exit;
        } else {
            header('Location: ../../frontend/pages/tambah_foto.php?error=' . urlencode('Gagal menyiapkan query database'));
            exit;
        }
    }

    header('Location: ../../frontend/pages/tambah_foto.php?error=' . urlencode('Gagal mengunggah gambar'));
    exit;
}