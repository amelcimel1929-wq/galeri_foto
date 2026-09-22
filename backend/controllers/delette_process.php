<?php
session_start();
// Sesuaikan path koneksi database Anda jika berbeda
require_once '../../config/koneksi.php'; 

// Pastikan request dikirim melalui metode POST atau GET dengan parameter ID
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // 1. Ambil nama file media/foto dari database sebelum record dihapus (opsional: untuk menghapus file fisik dari folder)
    $querySelect = "SELECT foto FROM media WHERE id = ?";
    $stmtSelect = mysqli_prepare($koneksi, $querySelect);
    mysqli_stmt_bind_param($stmtSelect, "i", $id);
    mysqli_stmt_execute($stmtSelect);
    $result = mysqli_stmt_get_result($stmtSelect);

    if ($row = mysqli_fetch_assoc($result)) {
        $filePath = "../../uploads/" . $row['foto'];
        // Hapus file fisik jika ada di server
        if (!empty($row['foto']) && file_exists($filePath)) {
            unlink($filePath);
        }
    }
    mysqli_stmt_close($stmtSelect);

    // 2. Hapus data dari database menggunakan Prepared Statement
    $queryDelete = "DELETE FROM media WHERE id = ?";
    $stmtDelete = mysqli_prepare($koneksi, $queryDelete);
    mysqli_stmt_bind_param($stmtDelete, "i", $id);

    if (mysqli_stmt_execute($stmtDelete)) {
        // Berhasil dihapus, arahkan kembali ke halaman galeri/index
        header("Location: ../../index.php?status=deleted_success");
        exit();
    } else {
        // Gagal menghapus dari database
        header("Location: ../detail.php?id=" . $id . "&error=delete_failed");
        exit();
    }

    mysqli_stmt_close($stmtDelete);
} else {
    // Jika diakses langsung tanpa kirim ID
    header("Location: ../../index.php");
    exit();
}