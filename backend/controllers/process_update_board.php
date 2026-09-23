<?php
session_start();
require_once __DIR__ . '/../config/connection.php';

if (!isset($_SESSION['id_user'])) {
    header('Location: ../../frontend/auth/login.php');
    exit;
}

$id_user_login = $_SESSION['id_user'];
$id_album = isset($_POST['id_album']) ? intval($_POST['id_album']) : 0;
$nama_album = trim($_POST['nama_album'] ?? '');

if ($id_album > 0 && !empty($nama_album)) {
    $qUpdate = "UPDATE album SET nama_album = ? WHERE id_album = ? AND id_user = ?";
    $stmt = $koneksi->prepare($qUpdate);
    $stmt->bind_param("sii", $nama_album, $id_album, $id_user_login);
    $stmt->execute();
    $stmt->close();
}

header('Location: ../../frontend/pages/detail_board.php?id_album=' . $id_album);
exit;