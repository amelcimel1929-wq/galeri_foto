<?php
session_start();
require_once __DIR__ . '/../config/connection.php';

if (!isset($_SESSION['id_user'])) {
    header('Location: ../../frontend/pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_album_favorit = isset($_POST['id_album_favorit']) ? intval($_POST['id_album_favorit']) : 0;
    $nama_album = isset($_POST['nama_album']) ? trim($_POST['nama_album']) : '';
    $id_user = $_SESSION['id_user'];

    if ($id_album_favorit > 0 && !empty($nama_album)) {
        // Update nama album favorit milik user yang sedang login
        $stmt = $koneksi->prepare("UPDATE album_favorit SET nama_album = ? WHERE id_album_favorit = ? AND id_user = ?");
        $stmt->bind_param("sii", $nama_album, $id_album_favorit, $id_user);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: ../../frontend/pages/detail_collage.php?id_album_favorit=" . $id_album_favorit);
    exit;
} else {
    header("Location: ../../frontend/pages/profile.php?tab=collages");
    exit;
}