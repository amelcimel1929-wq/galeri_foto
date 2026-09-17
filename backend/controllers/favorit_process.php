<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
include '../config/connection.php'; // Sesuaikan path koneksi database

$id_user = $_SESSION['id_user'] ?? null;

if (!$id_user) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Silakan login terlebih dahulu.'
    ]);
    exit;
}

// Ambil input JSON
$input = json_decode(file_get_contents('php://input'), true);
$id_foto = filter_var($input['id_foto'] ?? null, FILTER_VALIDATE_INT);

if (!$id_foto) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ID Foto tidak valid.'
    ]);
    exit;
}

try {
    // Cek apakah foto sudah disimpan oleh user
    $checkQuery = $koneksi->prepare("SELECT id_save FROM save_foto WHERE id_user = ? AND id_foto = ?");
    $checkQuery->bind_param("ii", $id_user, $id_foto);
    $checkQuery->execute();
    $resCheck = $checkQuery->get_result();

    if ($resCheck->num_rows > 0) {
        // Jika sudah ada -> Hapus (Unsave)
        $deleteQuery = $koneksi->prepare("DELETE FROM save_foto WHERE id_user = ? AND id_foto = ?");
        $deleteQuery->bind_param("ii", $id_user, $id_foto);
        $deleteQuery->execute();
        $deleteQuery->close();

        echo json_encode([
            'status' => 'success',
            'action' => 'removed',
            'message' => 'Dihapus dari favorit / collages'
        ]);
    } else {
        // Jika belum ada -> Simpan dengan tanggal_simpan saat ini (NOW())
        $insertQuery = $koneksi->prepare("INSERT INTO save_foto (id_foto, id_user, tanggal_simpan) VALUES (?, ?, NOW())");
        $insertQuery->bind_param("ii", $id_foto, $id_user);
        $insertQuery->execute();
        $insertQuery->close();

        echo json_encode([
            'status' => 'success',
            'action' => 'added',
            'message' => 'Disimpan ke favorit / collages'
        ]);
    }

    $checkQuery->close();
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal memproses request: ' . $e->getMessage()
    ]);
}