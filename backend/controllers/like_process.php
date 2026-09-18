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

// ---------- TOGGLE LIKE (LIKE / UNLIKE) ----------
if ($action === 'toggle') {
    $id_foto = (int) ($_POST['id_foto'] ?? 0);

    // Cek apakah user sudah menyukai foto ini
    $check = $koneksi->prepare("SELECT id_like FROM like_foto WHERE id_foto = ? AND id_user = ?");
    $check->bind_param("ii", $id_foto, $id_user);
    $check->execute();
    $is_liked = $check->get_result()->num_rows > 0;
    $check->close();

    if ($is_liked) {
        // Hapus like jika sudah menyukai
        $del = $koneksi->prepare("DELETE FROM like_foto WHERE id_foto = ? AND id_user = ?");
        $del->bind_param("ii", $id_foto, $id_user);
        $del->execute();
        $del->close();
        $liked_status = false;
    } else {
        // Tambahkan like baru
        $add = $koneksi->prepare("INSERT INTO like_foto (id_foto, id_user, tanggal_like) VALUES (?, ?, NOW())");
        $add->bind_param("ii", $id_foto, $id_user);
        $add->execute();
        $add->close();
        $liked_status = true;

        // --- TAMBAHAN NOTIFIKASI LIKE ---
        // Cari pemilik foto
        $qOwner = $koneksi->prepare("SELECT id_user FROM foto WHERE id_foto = ?");
        $qOwner->bind_param("i", $id_foto);
        $qOwner->execute();
        $resOwner = $qOwner->get_result()->fetch_assoc();
        $qOwner->close();

        $id_penerima = $resOwner['id_user'] ?? 0;

        // Kirim notifikasi jika yang menyukai bukan pemilik foto sendiri
        if ($id_penerima > 0 && $id_penerima != $id_user) {
            $pesan = "menyukai postingan Anda.";
            $notif = $koneksi->prepare("INSERT INTO notifikasi (id_user_penerima, id_user_pemicu, id_foto, pesan, tanggal_notifikasi) VALUES (?, ?, ?, ?, NOW())");
            $notif->bind_param("iiis", $id_penerima, $id_user, $id_foto, $pesan);
            $notif->execute();
            $notif->close();
        }
    }

    // Hitung total like terbaru
    $count = $koneksi->prepare("SELECT COUNT(*) as total FROM like_foto WHERE id_foto = ?");
    $count->bind_param("i", $id_foto);
    $count->execute();
    $total_like = $count->get_result()->fetch_assoc()['total'];
    $count->close();

    echo json_encode([
        'status'     => 'ok',
        'is_liked'   => $liked_status,
        'total_like' => $total_like
    ]);
    exit;
}

// ---------- STATUS LIKE ----------
if ($action === 'status') {
    $id_foto = (int) ($_GET['id_foto'] ?? 0);

    $check = $koneksi->prepare("SELECT id_like FROM like_foto WHERE id_foto = ? AND id_user = ?");
    $check->bind_param("ii", $id_foto, $id_user);
    $check->execute();
    $is_liked = $check->get_result()->num_rows > 0;
    $check->close();

    $count = $koneksi->prepare("SELECT COUNT(*) as total FROM like_foto WHERE id_foto = ?");
    $count->bind_param("i", $id_foto);
    $count->execute();
    $total_like = $count->get_result()->fetch_assoc()['total'];
    $count->close();

    echo json_encode([
        'is_liked'   => $is_liked,
        'total_like' => $total_like
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenali']);