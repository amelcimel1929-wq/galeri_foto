<?php
session_start();
require_once __DIR__ . '/../config/connection.php';

if (!isset($_SESSION['id_user'])) {
    header('Location: ../../frontend/pages/login.php');
    exit;
}

$id_user = $_SESSION['id_user'];
$action  = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $judul_foto      = trim($_POST['judul_foto'] ?? '');
    $deskripsi_foto  = trim($_POST['deskripsi_foto'] ?? '');
    $id_album        = $_POST['id_album'] ?? '';
    $nama_board_baru = trim($_POST['nama_board_baru'] ?? '');
    $file            = $_FILES['foto'] ?? null;

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

    $folder_upload = __DIR__ . '/../uploads/';
    if (!is_dir($folder_upload)) {
        mkdir($folder_upload, 0777, true);
    }

    $nama_file = 'foto_' . uniqid() . '_' . time() . '.' . $ekstensi;
    $tujuan    = $folder_upload . $nama_file;

    if (move_uploaded_file($file['tmp_name'], $tujuan)) {

        // 1. SIMPAN FOTO KE TABEL foto (tanggal_ungahan)
        $queryFoto = "INSERT INTO foto (id_user, judul_foto, deskripsi_foto, tanggal_ungahan, lokasi_file) VALUES (?, ?, ?, NOW(), ?)";
        $stmtFoto  = $koneksi->prepare($queryFoto);
        $stmtFoto->bind_param("isss", $id_user, $judul_foto, $deskripsi_foto, $nama_file);
        $stmtFoto->execute();
        $id_foto_baru = $koneksi->insert_id;
        $stmtFoto->close();

        // 2. JIKA MEMILIH BUAT BOARD BARU
        if ($id_album === 'new' && !empty($nama_board_baru)) {
            $stmtAlbum = $koneksi->prepare("INSERT INTO album (id_user, nama_album, deskripsi, tanggal_dibuat) VALUES (?, ?, ?, NOW())");
            $stmtAlbum->bind_param("iss", $id_user, $nama_board_baru, $deskripsi_foto);
            $stmtAlbum->execute();
            $id_album = $koneksi->insert_id;
            $stmtAlbum->close();
        }

        // 3. JIKA ALBUM DIPILIH ATAU BARU DIBUAT, SIMPAN KE TABEL save_foto
        if (!empty($id_album) && $id_album !== 'new') {
            $stmtSave = $koneksi->prepare("INSERT INTO save_foto (id_album, id_foto, id_user, tanggal_simpan) VALUES (?, ?, ?, NOW())");
            $stmtSave->bind_param("iii", $id_album, $id_foto_baru, $id_user);
            $stmtSave->execute();
            $stmtSave->close();
        }

        header('Location: ../../frontend/pages/profile.php?tab=boards');
        exit;
    }

    header('Location: ../../frontend/pages/tambah_foto.php?error=' . urlencode('Gagal mengunggah gambar'));
    exit;
}