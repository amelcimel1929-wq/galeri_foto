<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once __DIR__ . '/../config/connection.php';

// Cek login
if (!isset($_SESSION['id_user'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Belum login.']);
    exit();
}

$id_user = (int)$_SESSION['id_user'];

// Hitung followers (orang yang mem-follow kita)
$q_followers = $koneksi->prepare("SELECT COUNT(*) AS total FROM follow WHERE id_following = ?");
$q_followers->bind_param("i", $id_user);
$q_followers->execute();
$jumlah_followers = $q_followers->get_result()->fetch_assoc()['total'] ?? 0;
$q_followers->close();

// Hitung following (orang yang kita follow)
$q_following = $koneksi->prepare("SELECT COUNT(*) AS total FROM follow WHERE id_follower = ?");
$q_following->bind_param("i", $id_user);
$q_following->execute();
$jumlah_following = $q_following->get_result()->fetch_assoc()['total'] ?? 0;
$q_following->close();

echo json_encode([
    'status'    => 'success',
    'followers' => (int)$jumlah_followers,
    'following' => (int)$jumlah_following,
]);
exit();