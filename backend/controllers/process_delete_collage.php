<?php
session_start();
require_once __DIR__ . '/../config/connection.php';

if (!isset($_SESSION['id_user'])) {
    header('Location: ../../frontend/pages/login.php');
    exit;
}

$id_album_favorit = isset($_GET['id_album_favorit']) ? intval($_GET['id_album_favorit']) : 0;
$id_user = $_SESSION['id_user'];

if ($id_album_favorit > 0) {
    // 1. Verifikasi kepemilikan album favorit
    $stmtCheck = $koneksi->prepare("SELECT id_album_favorit FROM album_favorit WHERE id_album_favorit = ? AND id_user = ?");
    $stmtCheck->bind_param("ii", $id_album_favorit, $id_user);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();

    if ($resCheck->num_rows > 0) {
        // 2. Hapus data favorit yang ada di dalam album ini
        $stmtFav = $koneksi->prepare("DELETE FROM favorit WHERE id_album_favorit = ?");
        $stmtFav->bind_param("i", $id_album_favorit);
        $stmtFav->execute();
        $stmtFav->close();

        // 3. Hapus album favorit
        $stmtDel = $koneksi->prepare("DELETE FROM album_favorit WHERE id_album_favorit = ? AND id_user = ?");
        $stmtDel->bind_param("ii", $id_album_favorit, $id_user);
        $stmtDel->execute();
        $stmtDel->close();
    }
    $stmtCheck->close();
}

// Redirect kembali ke tab collages di profil
header("Location: ../../frontend/pages/profile.php?tab=collages");
exit;