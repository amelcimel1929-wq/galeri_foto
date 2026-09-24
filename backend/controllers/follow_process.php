<?php
session_start();

// Koneksi ke Database
require_once __DIR__ . '/../config/connection.php';

// Cek apakah user sudah login
if (!isset($_SESSION['id_user'])) {
    header("Location: /galeri_foto/frontend/pages/login.php");
    exit();
}

$id_follower  = (int)$_SESSION['id_user'];
$id_following = isset($_POST['id_following']) ? (int)$_POST['id_following'] : 0;
$follow_back = !empty($_POST['follow_back']);

// Validasi agar tidak bisa follow diri sendiri atau ID tidak valid
if ($id_following > 0 && $id_follower !== $id_following) {

    // 1. Cek apakah user sudah mem-follow target
    $check_sql = "SELECT id_follow FROM follow WHERE id_follower = ? AND id_following = ?";
    $stmt = $koneksi->prepare($check_sql);
    $stmt->bind_param("ii", $id_follower, $id_following);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($follow_back) {
        if ($res->num_rows === 0) {
            $ins_sql = "INSERT INTO follow (id_follower, id_following) VALUES (?, ?)";
            $ins_stmt = $koneksi->prepare($ins_sql);
            $ins_stmt->bind_param("ii", $id_follower, $id_following);
            $ins_stmt->execute();
            $ins_stmt->close();
        }
    } elseif ($res->num_rows > 0) {
        // --- PROSES UNFOLLOW ---
        $del_sql = "DELETE FROM follow WHERE id_follower = ? AND id_following = ?";
        $del_stmt = $koneksi->prepare($del_sql);
        $del_stmt->bind_param("ii", $id_follower, $id_following);
        $del_stmt->execute();
        $del_stmt->close();
    } else {
        // --- PROSES FOLLOW ---
        $ins_sql = "INSERT INTO follow (id_follower, id_following) VALUES (?, ?)";
        $ins_stmt = $koneksi->prepare($ins_sql);
        $ins_stmt->bind_param("ii", $id_follower, $id_following);

        if ($ins_stmt->execute()) {
            // --- TAMBAH NOTIFIKASI ---
            $pesan = "mulai mengikuti Anda.";
            $null_foto = NULL;

            // Query insert notifikasi khusus follow (id_foto diisi NULL)
            $notif_sql = "INSERT INTO notifikasi (id_user_penerima, id_user_pemicu, id_foto, tipe, pesan, is_read, tanggal_notifikasi) 
                          VALUES (?, ?, ?, 'follow', ?, 0, NOW())";
            $notif_stmt = $koneksi->prepare($notif_sql);
            $notif_stmt->bind_param("iiss", $id_following, $id_follower, $null_foto, $pesan);
            $notif_stmt->execute();
            $notif_stmt->close();
        }
        $ins_stmt->close();
    }
    $stmt->close();
}

// Redirect kembali ke halaman profil user yang di-follow/unfollow
header("Location: /galeri_foto/frontend/pages/profile_dimata_userlain.php?user_id=" . $id_following);
exit();
