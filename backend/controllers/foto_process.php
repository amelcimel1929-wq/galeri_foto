<?php
session_start();
require_once __DIR__ . '/../config/connection.php';

if (!isset($_SESSION['id_user'])) {
    header('Location: ../../frontend/pages/login.php');
    exit;
}

$id_user = $_SESSION['id_user'];
$action  = $_POST['action'] ?? $_GET['action'] ?? '';

// ---------- UPLOAD FOTO ----------
if ($action === 'upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul_foto     = trim($_POST['judul_foto'] ?? '');
    $deskripsi_foto = trim($_POST['deskripsi_foto'] ?? '');

    if (empty($judul_foto) || empty($_FILES['foto']['name'])) {
        header('Location: index.php?error=' . urlencode('Judul dan gambar wajib diisi'));
        exit;
    }

    $ekstensi_diizinkan = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $ekstensi = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

    if (!in_array($ekstensi, $ekstensi_diizinkan)) {
        header('Location: index.php?error=' . urlencode('Format gambar tidak didukung'));
        exit;
    }

    $folder_upload = __DIR__ . '/../uploads/';
    $nama_file     = 'foto_' . uniqid() . '_' . time() . '.' . $ekstensi;
    $tujuan        = $folder_upload . $nama_file;

    if (move_uploaded_file($_FILES['foto']['tmp_name'], $tujuan)) {
        $stmt = $koneksi->prepare(
            "INSERT INTO foto (id_user, judul_foto, deskripsi_foto, tanggal_unggahan, lokasi_file)
             VALUES (?, ?, ?, NOW(), ?)"
        );
        $stmt->execute([$id_user, $judul_foto, $deskripsi_foto, $nama_file]);

        header('Location: index.php');
        exit;
    }

    header('Location: index.php?error=' . urlencode('Gagal mengunggah gambar'));
    exit;
}

// ---------- TOGGLE LIKE ----------
if ($action === 'like') {
    $id_foto = (int) ($_POST['id_foto'] ?? 0);

    $cek = $koneksi->prepare("SELECT id_like FROM like_foto WHERE id_foto = ? AND id_user = ?");
    $cek->execute([$id_foto, $id_user]);
    $like = $cek->fetch(PDO::FETCH_ASSOC);

    if ($like) {
        $koneksi->prepare("DELETE FROM like_foto WHERE id_like = ?")->execute([$like['id_like']]);
        $status = 'unliked';
    } else {
        $koneksi->prepare(
            "INSERT INTO like_foto (id_foto, id_user, tanggal_like) VALUES (?, ?, NOW())"
        )->execute([$id_foto, $id_user]);
        $status = 'liked';
    }

    $hitung = $koneksi->prepare("SELECT COUNT(*) FROM like_foto WHERE id_foto = ?");
    $hitung->execute([$id_foto]);
    $total_like = (int) $hitung->fetchColumn();

    header('Content-Type: application/json');
    echo json_encode(['status' => $status, 'total_like' => $total_like]);
    exit;
}

// ---------- TOGGLE SAVE (ke album default "Tersimpan") ----------
if ($action === 'save') {
    $id_foto = (int) ($_POST['id_foto'] ?? 0);

    $cek_album = $koneksi->prepare("SELECT id_album FROM album WHERE id_user = ? AND nama_album = ?");
    $cek_album->execute([$id_user, 'Tersimpan']);
    $album = $cek_album->fetch(PDO::FETCH_ASSOC);

    if ($album) {
        $id_album = $album['id_album'];
    } else {
        $koneksi->prepare(
            "INSERT INTO album (id_user, nama_album, deskripsi, tanggal_dibuat)
             VALUES (?, 'Tersimpan', 'Koleksi foto yang disimpan', NOW())"
        )->execute([$id_user]);
        $id_album = $koneksi->lastInsertId();
    }

    $cek_save = $koneksi->prepare("SELECT id_save FROM save_foto WHERE id_foto = ? AND id_user = ?");
    $cek_save->execute([$id_foto, $id_user]);
    $save = $cek_save->fetch(PDO::FETCH_ASSOC);

    if ($save) {
        $koneksi->prepare("DELETE FROM save_foto WHERE id_save = ?")->execute([$save['id_save']]);
        $status = 'unsaved';
    } else {
        $koneksi->prepare(
            "INSERT INTO save_foto (id_album, id_foto, id_user, tanggal_simpan) VALUES (?, ?, ?, NOW())"
        )->execute([$id_album, $id_foto, $id_user]);
        $status = 'saved';
    }

    header('Content-Type: application/json');
    echo json_encode(['status' => $status]);
    exit;
}

header('Location: ../../frontend/pages/home.php');
exit;