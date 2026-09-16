<?php
session_start();
require_once __DIR__ . '/../config/connection.php';

// Pastikan selalu merespons dengan JSON
header('Content-Type: application/json');

// Matikan penanganan error HTML agar tidak merusak format JSON
error_reporting(0);

if (!isset($_SESSION['id_user'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

$id_user = $_SESSION['id_user'];
$action  = $_REQUEST['action'] ?? '';

// ---------- HAPUS KOMENTAR ----------
if ($action === 'delete') {
    $id_komentar = (int) ($_POST['id_komentar'] ?? 0);

    if ($id_komentar <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Komentar tidak valid']);
        exit;
    }

    // Cek dulu komentar itu ada dan memang milik user yang sedang login
    $cek = $koneksi->prepare("SELECT id_user FROM komentar_foto WHERE id_komentar = ?");
    $cek->bind_param("i", $id_komentar);
    $cek->execute();
    $row = $cek->get_result()->fetch_assoc();
    $cek->close();

    if (!$row) {
        echo json_encode(['status' => 'error', 'message' => 'Komentar tidak ditemukan']);
        exit;
    }

    if ((int) $row['id_user'] !== (int) $id_user) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Kamu tidak boleh menghapus komentar orang lain']);
        exit;
    }

    $stmt = $koneksi->prepare("DELETE FROM komentar_foto WHERE id_komentar = ? AND id_user = ?");
    $stmt->bind_param("ii", $id_komentar, $id_user);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'ok']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }

    $stmt->close();
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Aksi tidak valid']);