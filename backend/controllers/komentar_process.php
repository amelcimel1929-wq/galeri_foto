<?php
session_start();
require_once __DIR__ . '/../config/connection.php';

header('Content-Type: application/json');
error_reporting(0);

if (!isset($_SESSION['id_user'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

$id_user = $_SESSION['id_user'];
$action  = $_REQUEST['action'] ?? '';

// ---------- 1. TAMPILKAN DAFTAR KOMENTAR ----------
if ($action === 'list') {
    $id_foto = (int) ($_GET['id_foto'] ?? 0);

    $stmt = $koneksi->prepare(
        "SELECT k.id_komentar, k.parent_id, k.id_user, k.isi_komentar, k.tanggal_komentar, u.username
         FROM komentar_foto k
         JOIN user u ON k.id_user = u.id_user
         WHERE k.id_foto = ?
         ORDER BY k.id_komentar ASC"
    );
    $stmt->bind_param("i", $id_foto);
    $stmt->execute();
    $result = $stmt->get_result();

    $komentar = [];
    while ($row = $result->fetch_assoc()) {
        $komentar[] = [
            'id_komentar'  => (int) $row['id_komentar'],
            'parent_id'    => $row['parent_id'] !== null ? (int) $row['parent_id'] : null,
            'id_user'      => (int) $row['id_user'],
            'username'     => htmlspecialchars($row['username']),
            'isi_komentar' => htmlspecialchars($row['isi_komentar']),
            'tanggal'      => $row['tanggal_komentar']
        ];
    }

    echo json_encode($komentar);
    $stmt->close();
    exit;
}

// ---------- 2. TAMBAH KOMENTAR / BALASAN ----------
if ($action === 'add') {
    $id_foto      = (int) ($_POST['id_foto'] ?? 0);
    $isi_komentar = trim($_POST['isi_komentar'] ?? '');
    $parent_id    = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;

    if (empty($isi_komentar) || $id_foto <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Komentar tidak boleh kosong']);
        exit;
    }

    if ($parent_id !== null) {
        $cek = $koneksi->prepare("SELECT id_komentar FROM komentar_foto WHERE id_komentar = ? AND id_foto = ?");
        $cek->bind_param("ii", $parent_id, $id_foto);
        $cek->execute();
        if ($cek->get_result()->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Komentar yang dibalas tidak ditemukan']);
            $cek->close();
            exit;
        }
        $cek->close();
    }

    $tanggal = date('Y-m-d H:i:s');

    if ($parent_id !== null) {
        $stmt = $koneksi->prepare(
            "INSERT INTO komentar_foto (id_foto, parent_id, id_user, isi_komentar, tanggal_komentar)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("iiiss", $id_foto, $parent_id, $id_user, $isi_komentar, $tanggal);
    } else {
        $stmt = $koneksi->prepare(
            "INSERT INTO komentar_foto (id_foto, id_user, isi_komentar, tanggal_komentar)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("iiss", $id_foto, $id_user, $isi_komentar, $tanggal);
    }

    if ($stmt->execute()) {
        $inserted_id = $stmt->insert_id;

        // --- TAMBAHAN NOTIFIKASI KOMENTAR ---
        $qOwner = $koneksi->prepare("SELECT id_user FROM foto WHERE id_foto = ?");
        $qOwner->bind_param("i", $id_foto);
        $qOwner->execute();
        $resOwner = $qOwner->get_result()->fetch_assoc();
        $qOwner->close();

        $id_penerima = $resOwner['id_user'] ?? 0;

        if ($id_penerima > 0 && $id_penerima != $id_user) {
            $pesan = "mengomentari postingan Anda.";
            $notif = $koneksi->prepare("INSERT INTO notifikasi (id_user_penerima, id_user_pemicu, id_foto, pesan, tanggal_notifikasi) VALUES (?, ?, ?, ?, NOW())");
            $notif->bind_param("iiis", $id_penerima, $id_user, $id_foto, $pesan);
            $notif->execute();
            $notif->close();
        }

        echo json_encode(['status' => 'ok', 'id_komentar' => $inserted_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }

    $stmt->close();
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Aksi tidak valid']);