<?php
session_start();
require_once __DIR__ . '/../config/connection.php';

// Pastikan user sudah login
if (!isset($_SESSION['id_user'])) {
    header('Location: ../../frontend/auth/login.php');
    exit;
}

$id_user_login = $_SESSION['id_user'];
$id_foto = isset($_GET['id_foto']) ? intval($_GET['id_foto']) : 0;

if ($id_foto <= 0) {
    header('Location: ../../frontend/pages/profile.php');
    exit;
}

// 1. Cek keberadaan foto dan pastikan milik user yang sedang login
$qCheck = "SELECT lokasi_file FROM foto WHERE id_foto = ? AND id_user = ?";
$stmt = $koneksi->prepare($qCheck);
$stmt->bind_param("ii", $id_foto, $id_user_login);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    header('Location: ../../frontend/pages/profile.php');
    exit;
}

$foto = $res->fetch_assoc();
$stmt->close();

// 2. Hapus semua data terkait di tabel anak (sesuai ERD) agar tidak Foreign Key Error
$tablesWithIdFoto = ['komentar_foto', 'like_foto', 'favorit', 'save_foto', 'notifikasi'];

foreach ($tablesWithIdFoto as $table) {
    $qDelRelasi = "DELETE FROM {$table} WHERE id_foto = ?";
    $stmtRel = $koneksi->prepare($qDelRelasi);
    $stmtRel->bind_param("i", $id_foto);
    $stmtRel->execute();
    $stmtRel->close();
}

// 3. Hapus file gambar fisik di folder uploads
$file_path = __DIR__ . '/../uploads/' . $foto['lokasi_file'];
if (file_exists($file_path)) {
    unlink($file_path);
}

// 4. Hapus data utama di tabel foto
$qDelete = "DELETE FROM foto WHERE id_foto = ? AND id_user = ?";
$stmtDelete = $koneksi->prepare($qDelete);
$stmtDelete->bind_param("ii", $id_foto, $id_user_login);

if ($stmtDelete->execute()) {
    $stmtDelete->close();
    header('Location: ../../frontend/pages/profile.php');
    exit;
} else {
    $stmtDelete->close();
    echo "<script>
            alert('Gagal menghapus media.');
            window.location.href = '../../frontend/pages/detail.php?id=" . $id_foto . "';
          </script>";
    exit;
}
?>