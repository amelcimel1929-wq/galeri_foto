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

    // Cek username/email
    $cek = $koneksi->prepare("SELECT id_user FROM user WHERE username = ? OR email = ?");
    $cek->bind_param("ss", $username, $email);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        $cek->close();
        header('Location: ../../frontend/pages/login.php?error=' . urlencode('Username atau email sudah terdaftar'));
        exit;
    }
    $cek->close();

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert user baru
    $stmt = $koneksi->prepare(
        "INSERT INTO user (username, password, email, nama_lengkap, alamat) VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("sssss", $username, $password_hash, $email, $nama_lengkap, $alamat);

    if ($stmt->execute()) {
        $new_id = $stmt->insert_id;
        $stmt->close();

        // Otomatis login setelah daftar
        $_SESSION['id_user']      = $new_id;
        $_SESSION['username']     = $username;
        $_SESSION['nama_lengkap'] = $nama_lengkap;

        // Redirect langsung ke root index.php
        header('Location: ../../index.php');
        exit;
    } else {
        $stmt->close();
        header('Location: ../../frontend/pages/login.php?error=' . urlencode('Gagal mendaftar, coba lagi'));
        exit;
    }
}

// ---------- LOGIN ----------
if (isset($_POST['login'])) {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        header('Location: ../../frontend/pages/login.php?error=' . urlencode('Email dan kata sandi wajib diisi'));
        exit;
    }

    // Query Login
    $stmt = $koneksi->prepare("SELECT id_user, username, password, nama_lengkap FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['id_user']      = $user['id_user'];
            $_SESSION['username']     = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];

            // Redirect ke root index.php
            header('Location: ../../index.php');
            exit;
        }
    }

    $stmt->close();
    header('Location: ../../frontend/pages/login.php?error=' . urlencode('Email atau kata sandi salah'));
    exit;
}

header('Location: ../../frontend/pages/login.php');
exit;