<?php
session_start();
require_once __DIR__ . '/../config/connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$id_user = $_SESSION['id_user'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// 1. Ambil daftar album favorit
if ($action === 'get_albums') {
    $stmt = $koneksi->prepare("SELECT id_album_favorit, nama_album FROM album_favorit WHERE id_user = ? ORDER BY id_album_favorit DESC");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['status' => 'ok', 'albums' => $result]);
    exit;
}

// 2. Buat album favorit baru
if ($action === 'create_album') {
    $nama_album = trim($_POST['nama_album'] ?? '');
    if (empty($nama_album)) {
        echo json_encode(['status' => 'error', 'message' => 'Nama album tidak boleh kosong']);
        exit;
    }
    
    $stmt = $koneksi->prepare("INSERT INTO album_favorit (id_user, nama_album, tanggal_dibuat) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $id_user, $nama_album);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'ok', 'id_album_favorit' => $stmt->insert_id, 'nama_album' => $nama_album]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal membuat album']);
    }
    exit;
}

// 3. Simpan atau Hapus dari Favorit (Toggle)
if ($action === 'toggle') {
    $id_foto = intval($_POST['id_foto'] ?? 0);
    $id_album_favorit = !empty($_POST['id_album_favorit']) ? intval($_POST['id_album_favorit']) : NULL;

    $check = $koneksi->prepare("SELECT id_favorit FROM favorit WHERE id_user = ? AND id_foto = ?");
    $check->bind_param("ii", $id_user, $id_foto);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        // Hapus dari favorit
        $del = $koneksi->prepare("DELETE FROM favorit WHERE id_user = ? AND id_foto = ?");
        $del->bind_param("ii", $id_user, $id_foto);
        $del->execute();
        echo json_encode(['status' => 'ok', 'is_favorited' => false]);
    } else {
        // Simpan ke favorit
        if ($id_album_favorit) {
            $ins = $koneksi->prepare("INSERT INTO favorit (id_user, id_foto, id_album_favorit, tanggal_favorit) VALUES (?, ?, ?, NOW())");
            $ins->bind_param("iii", $id_user, $id_foto, $id_album_favorit);
        } else {
            $ins = $koneksi->prepare("INSERT INTO favorit (id_user, id_foto, tanggal_favorit) VALUES (?, ?, NOW())");
            $ins->bind_param("ii", $id_user, $id_foto);
        }
        $ins->execute();

        // --- TAMBAHAN NOTIFIKASI FAVORIT ---
        $qOwner = $koneksi->prepare("SELECT id_user FROM foto WHERE id_foto = ?");
        $qOwner->bind_param("i", $id_foto);
        $qOwner->execute();
        $resOwner = $qOwner->get_result()->fetch_assoc();
        $qOwner->close();

        $id_penerima = $resOwner['id_user'] ?? 0;

        if ($id_penerima > 0 && $id_penerima != $id_user) {
            $pesan = "menambahkan postingan Anda ke favorit.";
            $notif = $koneksi->prepare("INSERT INTO notifikasi (id_user_penerima, id_user_pemicu, id_foto, pesan, tanggal_notifikasi) VALUES (?, ?, ?, ?, NOW())");
            $notif->bind_param("iiis", $id_penerima, $id_user, $id_foto, $pesan);
            $notif->execute();
            $notif->close();
        }

        echo json_encode(['status' => 'ok', 'is_favorited' => true]);
    }
    exit;
}

// 4. Cek status awal favorit
if ($action === 'status') {
    $id_foto = intval($_GET['id_foto'] ?? 0);
    $stmt = $koneksi->prepare("SELECT id_favorit, id_album_favorit FROM favorit WHERE id_user = ? AND id_foto = ?");
    $stmt->bind_param("ii", $id_user, $id_foto);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    
    echo json_encode([
        'is_favorited' => !empty($data),
        'id_album_favorit' => $data['id_album_favorit'] ?? null
    ]);
    exit;
}