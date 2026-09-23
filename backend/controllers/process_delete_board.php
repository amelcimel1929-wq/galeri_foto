<?php
session_start();
require_once __DIR__ . '/../config/connection.php';

if (!isset($_SESSION['id_user'])) {
    header('Location: ../../frontend/auth/login.php');
    exit;
}

$id_user_login = $_SESSION['id_user'];
$id_album = isset($_GET['id_album']) ? intval($_GET['id_album']) : 0;

if ($id_album > 0) {
    // 1. Hapus simpanan foto di tabel save_foto terlebih dahulu agar tidak Foreign Key Error
    $qDelSave = "DELETE FROM save_foto WHERE id_album = ?";
    $stmtS = $koneksi->prepare($qDelSave);
    $stmtS->bind_param("i", $id_album);
    $stmtS->execute();
    $stmtS->close();

    // 2. Hapus album utama
    $qDelAlbum = "DELETE FROM album WHERE id_album = ? AND id_user = ?";
    $stmtA = $koneksi->prepare($qDelAlbum);
    $stmtA->bind_param("ii", $id_album, $id_user_login);
    $stmtA->execute();
    $stmtA->close();
}

header('Location: ../../frontend/pages/profile.php');
exit;