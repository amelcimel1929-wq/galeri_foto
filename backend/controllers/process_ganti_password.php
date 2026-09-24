<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/connection.php';

// Pastikan request menggunakan method POST dan user sudah login
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['id_user'])) {
    header("Location: /galeri_foto/frontend/pages/login.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validasi Form Kosong
if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    $_SESSION['error_msg'] = "Semua kolom wajib diisi.";
    header("Location: /galeri_foto/frontend/pages/ganti_password.php");
    exit();
}

// Validasi Kesesuaian Password Baru
if ($new_password !== $confirm_password) {
    $_SESSION['error_msg'] = "Konfirmasi kata sandi baru tidak cocok.";
    header("Location: /galeri_foto/frontend/pages/ganti_password.php");
    exit();
}

// Minimal Panjang Password
if (strlen($new_password) < 6) {
    $_SESSION['error_msg'] = "Kata sandi baru minimal 6 karakter.";
    header("Location: /galeri_foto/frontend/pages/ganti_password.php");
    exit();
}

// Ambil password lama dari database
$stmt = $koneksi->prepare("SELECT password FROM user WHERE id_user = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    $db_password = $row['password'];

    // Cek password lama (Mendukung password_hash & MD5/Plain teks jika ada sisa data lama)
    $is_valid = false;
    if (password_verify($current_password, $db_password)) {
        $is_valid = true;
    } elseif (md5($current_password) === $db_password || $current_password === $db_password) {
        $is_valid = true;
    }

    if (!$is_valid) {
        $_SESSION['error_msg'] = "Kata sandi saat ini salah.";
        header("Location: /galeri_foto/frontend/pages/ganti_password.php");
        exit();
    }

    // Hash password baru & update database
    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
    $updateStmt = $koneksi->prepare("UPDATE user SET password = ? WHERE id_user = ?");
    $updateStmt->bind_param("si", $hashed_new_password, $id_user);

    if ($updateStmt->execute()) {
        $_SESSION['success_msg'] = "Kata sandi berhasil diperbarui.";
    } else {
        $_SESSION['error_msg'] = "Gagal memperbarui kata sandi. Silakan coba lagi.";
    }
    $updateStmt->close();
} else {
    $_SESSION['error_msg'] = "Pengguna tidak ditemukan.";
}

$stmt->close();
header("Location: /galeri_foto/frontend/pages/ganti_password.php");
exit();