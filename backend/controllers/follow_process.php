<?php
session_start();
require_once __DIR__ . '/../config/connection.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: /galeri_foto/frontend/pages/login.php");
    exit;
}

$id_follower = $_SESSION['id_user'];
$id_following = isset($_POST['id_following']) ? intval($_POST['id_following']) : 0;

if ($id_following > 0 && $id_follower !== $id_following) {
    // Cek status apakah sudah di-follow
    $check_sql = "SELECT id_follow FROM follow WHERE id_follower = ? AND id_following = ?";
    $stmt = $koneksi->prepare($check_sql);
    $stmt->bind_param("ii", $id_follower, $id_following);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        // UNFOLLOW
        $del_sql = "DELETE FROM follow WHERE id_follower = ? AND id_following = ?";
        $del_stmt = $koneksi->prepare($del_sql);
        $del_stmt->bind_param("ii", $id_follower, $id_following);
        $del_stmt->execute();
    } else {
        // FOLLOW
        $ins_sql = "INSERT INTO follow (id_follower, id_following) VALUES (?, ?)";
        $ins_stmt = $koneksi->prepare($ins_sql);
        $ins_stmt->bind_param("ii", $id_follower, $id_following);
        if ($ins_stmt->execute()) {
            // Tambahkan Notifikasi
            $pesan = "mulai mengikuti Anda.";
            $notif_sql = "INSERT INTO notifikasi (id_user_penerima, id_user_pemicu, id_foto, tipe, pesan, is_read, tanggal_notifikasi) 
                          VALUES (?, ?, NULL, 'follow', ?, 0, NOW())";
            $notif_stmt = $koneksi->prepare($notif_sql);
            $notif_stmt->bind_param("iis", $id_following, $id_follower, $pesan);
            $notif_stmt->execute();
        }
    }
}

// Redirect kembali ke halaman profil tujuan
header("Location: /galeri_foto/frontend/pages/profile_dimata_userlain.php?user_id=" . $id_following);
exit;