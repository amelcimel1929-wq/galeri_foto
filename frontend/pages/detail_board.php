<?php
session_start();
require_once __DIR__ . '/../../backend/config/connection.php';

$id_user_login = $_SESSION['id_user'] ?? null;
if (!$id_user_login) {
    header('Location: ../auth/login.php');
    exit;
}

$id_album = (int) ($_GET['id_album'] ?? 0);
if (!$id_album) {
    header('Location: profile.php');
    exit;
}

// 1. Query Data Album — TIDAK dibatasi hanya milik user yang login,
// supaya board pengguna lain juga bisa dibuka lewat profil mereka.
// Sekalian JOIN ke tabel user biar tahu siapa pemilik album ini.
$qAlbum = "SELECT a.*, u.username, u.nama_lengkap 
           FROM album a 
           JOIN user u ON a.id_user = u.id_user 
           WHERE a.id_album = ?";
$stmtA = $koneksi->prepare($qAlbum);
$stmtA->bind_param("i", $id_album);
$stmtA->execute();
$album = $stmtA->get_result()->fetch_assoc();
$stmtA->close();

if (!$album) {
    echo "<p style='padding:20px;'>Album tidak ditemukan.</p>";
    exit;
}

// Cek apakah board ini milik user yang sedang login atau bukan,
// dipakai untuk menentukan tombol "Kembali" mengarah ke profil siapa.
$id_pemilik_album = (int) $album['id_user'];
$is_own_board     = ($id_pemilik_album === (int) $id_user_login);

$link_kembali = $is_own_board
    ? 'profile.php'
    : 'profile_dimata_userlain.php?user_id=' . $id_pemilik_album;

$nama_pemilik = !empty($album['nama_lengkap']) ? $album['nama_lengkap'] : $album['username'];

// 2. Query Foto dalam Album
$qFotos = "SELECT f.* FROM save_foto sf JOIN foto f ON sf.id_foto = f.id_foto WHERE sf.id_album = ? ORDER BY sf.tanggal_simpan DESC";
$stmtF = $koneksi->prepare($qFotos);
$stmtF->bind_param("i", $id_album);
$stmtF->execute();
$resFotos = $stmtF->get_result();

include '../partials/header.php';
include '../partials/navbar.php';
?>

<style>
    .board-detail-container {
        max-width: 1200px;
        margin: 30px auto;
        padding: 0 20px;
    }
    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        color: #111;
        font-weight: 600;
        margin-bottom: 20px;
    }
    .board-header { margin-bottom: 24px; }
    .board-title-main { font-size: 2rem; font-weight: 700; margin: 0 0 6px 0; }
    .board-subtitle { color: #767676; font-size: 0.9rem; margin: 0; }
    .board-owner { color: #767676; font-size: 0.9rem; margin: 2px 0 0 0; }
    .board-owner a { color: #111; font-weight: 600; text-decoration: none; }
    .board-owner a:hover { text-decoration: underline; }

    /* LAYOUT FLEXBOX: Foto berjejer ke samping, lalu turun ke bawah jika penuh */
    .flex-gallery {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        align-items: flex-start;
    }
    .gallery-item {
        display: block;
        text-decoration: none;
        background: #f0f0f0;
        border-radius: 16px;
        overflow: hidden;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .gallery-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.1);
    }
    .gallery-item img {
        display: block;
        max-width: 260px; /* Lebar maksimal tiap foto */
        height: auto;     /* Mempertahankan rasio asli (landscape/portrait) */
        border-radius: 16px;
    }

    /* MODAL LIGHTBOX FULLSCREEN */
    .lightbox-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.85);
        backdrop-filter: blur(5px);
        justify-content: center;
        align-items: center;
    }
    .lightbox-modal.active {
        display: flex;
    }
    .lightbox-img {
        max-width: 90vw;
        max-height: 90vh;
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }
    .lightbox-close {
        position: absolute;
        top: 20px;
        right: 25px;
        color: #fff;
        font-size: 32px;
        font-weight: bold;
        cursor: pointer;
        user-select: none;
        transition: color 0.2s;
    }
    .lightbox-close:hover {
        color: #ff4d4d;
    }
</style>

<main class="main-content">
    <div class="board-detail-container">
        <a href="<?= htmlspecialchars($link_kembali); ?>" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Boards
        </a>

        <div class="board-header">
            <h1 class="board-title-main"><?= htmlspecialchars($album['nama_album']); ?></h1>
            <p class="board-subtitle"><?= $resFotos->num_rows; ?> Pin</p>
            <?php if (!$is_own_board): ?>
                <p class="board-owner">
                    Oleh <a href="profile_dimata_userlain.php?user_id=<?= $id_pemilik_album; ?>">
                        <?= htmlspecialchars($nama_pemilik); ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>

        <!-- Galeri Foto Flexbox -->
        <div class="flex-gallery">
            <?php if ($resFotos->num_rows > 0): ?>
                <?php while ($foto = $resFotos->fetch_assoc()): ?>
                    <a href="detail.php?id=<?= $foto['id_foto']; ?>" class="gallery-item">
                        <img src="../../backend/uploads/<?= htmlspecialchars($foto['lokasi_file']); ?>" 
                             alt="<?= htmlspecialchars($foto['judul_foto'] ?? 'Foto Board'); ?>">
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: #767676;">Belum ada foto dalam board ini.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Modal Fullscreen / Lightbox -->
<div id="lightboxModal" class="lightbox-modal" onclick="closeLightbox(event)">
    <span class="lightbox-close" onclick="closeLightbox(event)">&times;</span>
    <img id="lightboxImg" class="lightbox-img" src="" alt="Full Image">
</div>

<script>
function openLightbox(imageSrc) {
    const modal = document.getElementById('lightboxModal');
    const modalImg = document.getElementById('lightboxImg');
    modalImg.src = imageSrc;
    modal.classList.add('active');
}

function closeLightbox(event) {
    // Tutup jika klik tombol close atau area hitam di luar gambar
    if (event.target.id === 'lightboxModal' || event.target.classList.contains('lightbox-close')) {
        const modal = document.getElementById('lightboxModal');
        modal.classList.remove('active');
    }
}
</script>

</body>
</html>