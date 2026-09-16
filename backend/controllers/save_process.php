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

// ---------- TOGGLE SIMPAN FOTO ----------
if ($action === 'toggle') {
    $id_foto  = (int) ($_POST['id_foto'] ?? 0);
    $id_album = (int) ($_POST['id_album'] ?? 0);

    $check = $koneksi->prepare("SELECT id_save FROM save_foto WHERE id_foto = ? AND id_user = ?");
    $check->bind_param("ii", $id_foto, $id_user);
    $check->execute();
    $is_saved = $check->get_result()->num_rows > 0;
    $check->close();

    if ($is_saved) {
        $del = $koneksi->prepare("DELETE FROM save_foto WHERE id_foto = ? AND id_user = ?");
        $del->bind_param("ii", $id_foto, $id_user);
        $del->execute();
        $del->close();
        $saved_status = false;
    } else {
        $add = $koneksi->prepare("INSERT INTO save_foto (id_album, id_foto, id_user, tanggal_simpan) VALUES (?, ?, ?, NOW())");
        $add->bind_param("iii", $id_album, $id_foto, $id_user);
        $add->execute();
        $add->close();
        $saved_status = true;
    }

    echo json_encode([
        'status'   => 'ok',
        'is_saved' => $saved_status
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenali']);