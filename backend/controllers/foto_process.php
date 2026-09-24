<?php
session_start();
require_once __DIR__ . '/../config/connection.php';

if (!isset($_SESSION['id_user'])) {
    // Jika request via AJAX, kembalikan respon JSON
    if (isset($_POST['action']) && in_array($_POST['action'], ['update_visibilitas', 'delete'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Sesi tidak valid / belum login.']);
        exit;
    }
    
    header('Location: ../../frontend/pages/login.php');
    exit;
}

$id_user = $_SESSION['id_user'];
$action  = $_POST['action'] ?? $_GET['action'] ?? '';

function hapusRelasiFoto(mysqli $db, int $idFoto): void {
    foreach (['komentar_foto', 'like_foto', 'favorit', 'save_foto', 'notifikasi'] as $table) {
        $stmt = $db->prepare("DELETE FROM {$table} WHERE id_foto = ?");
        if ($stmt) { $stmt->bind_param('i', $idFoto); $stmt->execute(); $stmt->close(); }
    }
}

// --- AKSI 1: UPLOAD FOTO (ASLI & KODE UTAMA KAMU) ---
if ($action === 'upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $judul_foto      = trim($_POST['judul_foto'] ?? '');
    $deskripsi_foto  = trim($_POST['deskripsi_foto'] ?? '');
    $id_album        = $_POST['id_album'] ?? '';
    $nama_board_baru = trim($_POST['nama_board_baru'] ?? '');
    $file            = $_FILES['foto'] ?? null;

    if (empty($judul_foto) || !$file || empty($file['name'])) {
        header('Location: ../../frontend/pages/tambah_foto.php?error=' . urlencode('Judul dan gambar wajib diisi'));
        exit;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        header('Location: ../../frontend/pages/tambah_foto.php?error=' . urlencode('Gagal membaca file gambar'));
        exit;
    }

    $ekstensi_diizinkan = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $ekstensi           = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ekstensi, $ekstensi_diizinkan)) {
        header('Location: ../../frontend/pages/tambah_foto.php?error=' . urlencode('Format gambar tidak didukung'));
        exit;
    }

    $folder_upload = __DIR__ . '/../uploads/';
    if (!is_dir($folder_upload)) {
        mkdir($folder_upload, 0777, true);
    }

    $nama_file = 'foto_' . uniqid() . '_' . time() . '.' . $ekstensi;
    $tujuan    = $folder_upload . $nama_file;

    if (move_uploaded_file($file['tmp_name'], $tujuan)) {

        // 1. SIMPAN FOTO KE TABEL foto (tanggal_ungahan)
        $queryFoto = "INSERT INTO foto (id_user, judul_foto, deskripsi_foto, tanggal_ungahan, lokasi_file) VALUES (?, ?, ?, NOW(), ?)";
        $stmtFoto  = $koneksi->prepare($queryFoto);
        $stmtFoto->bind_param("isss", $id_user, $judul_foto, $deskripsi_foto, $nama_file);
        $stmtFoto->execute();
        $id_foto_baru = $koneksi->insert_id;
        $stmtFoto->close();

        // 2. JIKA MEMILIH BUAT BOARD BARU
        if ($id_album === 'new' && !empty($nama_board_baru)) {
            $stmtAlbum = $koneksi->prepare("INSERT INTO album (id_user, nama_album, deskripsi, tanggal_dibuat) VALUES (?, ?, ?, NOW())");
            $stmtAlbum->bind_param("iss", $id_user, $nama_board_baru, $deskripsi_foto);
            $stmtAlbum->execute();
            $id_album = $koneksi->insert_id;
            $stmtAlbum->close();
        }

        // 3. JIKA ALBUM DIPILIH ATAU BARU DIBUAT, SIMPAN KE TABEL save_foto
        if (!empty($id_album) && $id_album !== 'new') {
            $stmtSave = $koneksi->prepare("INSERT INTO save_foto (id_album, id_foto, id_user, tanggal_simpan) VALUES (?, ?, ?, NOW())");
            $stmtSave->bind_param("iii", $id_album, $id_foto_baru, $id_user);
            $stmtSave->execute();
            $stmtSave->close();
        }

        header('Location: ../../frontend/pages/profile.php?tab=boards');
        exit;
    }

    header('Location: ../../frontend/pages/tambah_foto.php?error=' . urlencode('Gagal mengunggah gambar'));
    exit;
}

// --- AKSI 2: UPDATE VISIBILITAS (PRIVATE / PUBLIC) ---
if ($action === 'update_visibilitas' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $id_foto = isset($_POST['id_foto']) ? intval($_POST['id_foto']) : 0;
    $visibilitas = $_POST['visibilitas'] ?? 'public';

    if (!in_array($visibilitas, ['public', 'private'])) {
        echo json_encode(['status' => 'error', 'message' => 'Status visibilitas tidak valid.']);
        exit;
    }

    // Update kolom visibilitas di tabel foto
    $query = "UPDATE foto SET visibilitas = ? WHERE id_foto = ? AND id_user = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("sii", $visibilitas, $id_foto, $id_user);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'ok', 'visibilitas' => $visibilitas]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate visibilitas foto.']);
    }
    $stmt->close();
    exit;
}

if ($action === 'update_details' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $id_foto = (int)($_POST['id_foto'] ?? 0);
    $judul = trim($_POST['judul_foto'] ?? '');
    $deskripsi = trim($_POST['deskripsi_foto'] ?? '');
    if ($id_foto <= 0 || $judul === '') { echo json_encode(['status'=>'error','message'=>'Judul wajib diisi.']); exit; }
    $stmt = $koneksi->prepare('UPDATE foto SET judul_foto = ?, deskripsi_foto = ? WHERE id_foto = ? AND id_user = ?');
    $stmt->bind_param('ssii', $judul, $deskripsi, $id_foto, $id_user);
    $ok = $stmt->execute();
    $stmt->close();
    echo json_encode(['status'=>$ok?'ok':'error','judul_foto'=>$judul,'deskripsi_foto'=>$deskripsi]);
    exit;
}

// --- AKSI 3: HAPUS MEDIA ---
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $id_foto = isset($_POST['id_foto']) ? intval($_POST['id_foto']) : 0;

    // Ambil info berkas terlebih dahulu
    $stmtSelect = $koneksi->prepare("SELECT lokasi_file FROM foto WHERE id_foto = ? AND id_user = ?");
    $stmtSelect->bind_param("ii", $id_foto, $id_user);
    $stmtSelect->execute();
    $res = $stmtSelect->get_result()->fetch_assoc();
    $stmtSelect->close();

    if ($res) {
        // Hapus file dari folder uploads jika ada
        $filePath = __DIR__ . '/../uploads/' . $res['lokasi_file'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        hapusRelasiFoto($koneksi, $id_foto);
        // Hapus record dari database
        $stmtDelete = $koneksi->prepare("DELETE FROM foto WHERE id_foto = ? AND id_user = ?");
        $stmtDelete->bind_param("ii", $id_foto, $id_user);
        $stmtDelete->execute();
        $stmtDelete->close();

        echo json_encode(['status' => 'ok']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Foto tidak ditemukan atau Anda tidak memiliki hak akses.']);
    }
    exit;
}
