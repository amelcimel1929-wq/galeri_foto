<?php
session_start();
require_once __DIR__ . '/../config/connection.php';

// ---------- LOGOUT ----------
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: ../../frontend/pages/login.php');
    exit;
}

// ---------- REGISTER ----------
if (isset($_POST['register'])) {
    $username     = trim($_POST['username'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $password     = $_POST['password'] ?? '';
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $alamat       = trim($_POST['alamat'] ?? '');

    if (empty($username) || empty($email) || empty($password) || empty($nama_lengkap) || empty($alamat)) {
        header('Location: ../../frontend/pages/login.php?error=' . urlencode('Semua field wajib diisi'));
        exit;
    }

    $cek = $koneksi->prepare("SELECT id_user FROM user WHERE username = ? OR email = ?");
    $cek->execute([$username, $email]);

    if ($cek->rowCount() > 0) {
        header('Location: ../../frontend/pages/login.php?error=' . urlencode('Username atau email sudah terdaftar'));
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $koneksi->prepare(
        "INSERT INTO user (username, password, email, nama_lengkap, alamat)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([$username, $password_hash, $email, $nama_lengkap, $alamat]);

    header('Location: ../../frontend/pages/login.php?success=' . urlencode('Pendaftaran berhasil, silakan masuk'));
    exit;
}

// ---------- LOGIN ----------
if (isset($_POST['login'])) {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        header('Location: ../../frontend/pages/login.php?error=' . urlencode('Email dan kata sandi wajib diisi'));
        exit;
    }

    $stmt = $koneksi->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['id_user']      = $user['id_user'];
        $_SESSION['username']     = $user['username'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];

        header('Location: index.php');
        exit;
    }

    header('Location: ../../frontend/pages/login.php?error=' . urlencode('Email atau kata sandi salah'));
    exit;
}

header('Location: ../../frontend/pages/login.php');
exit;