<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/connection.php';

function cariFoto($koneksi, $keyword = '') {
    $keyword = trim($keyword);
    
    if (!empty($keyword)) {
        $keyword_clean = "%" . $koneksi->real_escape_string($keyword) . "%";
        
        // Sesuaikan nama kolom dan tabel dengan database Anda
        $stmt = $koneksi->prepare("SELECT * FROM foto WHERE judul_foto LIKE ? OR deskripsi_foto LIKE ? ORDER BY id_foto DESC");
        $stmt->bind_param("ss", $keyword_clean, $keyword_clean);
        $stmt->execute();
        return $stmt->get_result();
    } else {
        // Tampilkan semua foto jika tidak ada kata kunci
        return $koneksi->query("SELECT * FROM foto ORDER BY id_foto DESC");
    }
}
?>