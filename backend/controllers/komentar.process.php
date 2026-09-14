<?php
session_start();
require_once __DIR__ . '/../config/connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['id_user'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Silakan masuk terlebih dahulu']);
    exit;
}

$id_user = $_SESSION['id_user'];
$action  = $_GET['action'] ?? $_POST['action'] ?? '';

// ---------- DAFTAR KOMENTAR ----------
if ($action === 'list') {
    $id_foto = (int) ($_GET['id_foto'] ?? 0);

    $stmt = $koneksi->prepare(
        "SELECT komentar_foto.isi_komentar, komentar_foto.tanggal_komentar, user.username
         FROM komentar_foto
         JOIN user ON komentar_foto.id_user = user.id_user
         WHERE komentar_foto.id_foto = ?
         ORDER BY komentar_foto.tanggal_komentar ASC"
    );
    $stmt->execute([$id_foto]);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// ---------- TAMBAH KOMENTAR ----------
if ($action === 'add') {
    $id_foto      = (int) ($_POST['id_foto'] ?? 0);
    $isi_komentar = trim($_POST['isi_komentar'] ?? '');

    if (empty($isi_komentar)) {
        echo json_encode(['status' => 'error', 'message' => 'Komentar tidak boleh kosong']);
        exit;
    }

    $stmt = $koneksi->prepare(
        "INSERT INTO komentar_foto (id_foto, id_user, isi_komentar, tanggal_komentar)
         VALUES (?, ?, ?, NOW())"
    );
    $stmt->execute([$id_foto, $id_user, $isi_komentar]);

    echo json_encode(['status' => 'ok']);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenali']);