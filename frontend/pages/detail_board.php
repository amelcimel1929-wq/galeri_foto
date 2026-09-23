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

// 1. Query Data Album
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

<!-- Import Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .board-detail-container {
        width: 100%;
        max-width: 100%;
        margin: 20px 0;
        padding: 0 24px;
        box-sizing: border-box;
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
    
    /* Layout Judul & Action Buttons */
    .board-title-wrapper {
        display: inline-flex;
        align-items: center;
        gap: 12px;
    }
    .board-title-main { font-size: 2rem; font-weight: 700; margin: 0; }
    
    .btn-action-icon {
        background: #efefef;
        border: none;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        color: #333;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background-color 0.2s ease, transform 0.2s ease;
        text-decoration: none;
        font-size: 15px;
    }
    .btn-action-icon:hover {
        background-color: #e2e2e2;
        transform: scale(1.08);
    }
    .btn-action-icon.delete:hover {
        background-color: #ffebe9;
        color: #e53935;
    }

    .board-subtitle { color: #767676; font-size: 0.9rem; margin: 6px 0 0 0; }
    .board-owner { color: #767676; font-size: 0.9rem; margin: 2px 0 0 0; }
    .board-owner a { color: #111; font-weight: 600; text-decoration: none; }
    .board-owner a:hover { text-decoration: underline; }

    /* LAYOUT FLEXBOX: Foto berjejer ke samping */
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
        max-width: 260px;
        height: auto;
        border-radius: 16px;
    }

    /* MODAL RENAME ALBUM */
    .edit-modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        backdrop-filter: blur(3px);
    }
    .edit-modal-overlay.active { display: flex; }
    .edit-modal-box {
        background: #ffffff;
        padding: 24px;
        border-radius: 16px;
        width: 100%;
        max-width: 380px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }
    .edit-modal-box h3 { margin: 0 0 16px 0; font-size: 18px; color: #111; }
    .edit-modal-box input[type="text"] {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
        box-sizing: border-box;
        margin-bottom: 20px;
        outline: none;
    }
    .edit-modal-box input[type="text"]:focus { border-color: #000; }
    .edit-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    .btn-modal {
        padding: 8px 16px;
        border-radius: 20px;
        border: none;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-modal-cancel { background-color: #efefef; color: #111; }
    .btn-modal-save { background-color: #e60023; color: #fff; }

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
    .lightbox-modal.active { display: flex; }
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
    .lightbox-close:hover { color: #ff4d4d; }
</style>

<main class="main-content">
    <div class="board-detail-container">
        <a href="<?= htmlspecialchars($link_kembali); ?>" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Boards
        </a>

        <div class="board-header">
            <div class="board-title-wrapper">
                <h1 class="board-title-main"><?= htmlspecialchars($album['nama_album']); ?></h1>

                <?php if ($is_own_board): ?>
                    <!-- Icon Edit Nama Album -->
                    <button type="button" class="btn-action-icon" onclick="openRenameModal()" title="Ubah Nama Album">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    
                    <!-- Icon Hapus Album -->
                    <a href="../../backend/controllers/process_delete_board.php?id_album=<?= $album['id_album']; ?>" 
                       class="btn-action-icon delete" 
                       title="Hapus Album"
                       onclick="return confirm('Apakah Anda yakin ingin menghapus album ini? Foto yang tersimpan di dalamnya akan terlepas dari album ini.')">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                <?php endif; ?>
            </div>

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

<!-- Modal Rename Album -->
<?php if ($is_own_board): ?>
<div id="renameModal" class="edit-modal-overlay">
    <div class="edit-modal-box">
        <h3>Ubah Nama Album</h3>
        <form action="../../backend/controllers/process_update_board.php" method="POST">
            <input type="hidden" name="id_album" value="<?= $album['id_album']; ?>">
            <input type="text" name="nama_album" value="<?= htmlspecialchars($album['nama_album']); ?>" required>
            <div class="edit-modal-actions">
                <button type="button" class="btn-modal btn-modal-cancel" onclick="closeRenameModal()">Batal</button>
                <button type="submit" class="btn-modal btn-modal-save">Simpan</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Modal Fullscreen / Lightbox -->
<div id="lightboxModal" class="lightbox-modal" onclick="closeLightbox(event)">
    <span class="lightbox-close" onclick="closeLightbox(event)">&times;</span>
    <img id="lightboxImg" class="lightbox-img" src="" alt="Full Image">
</div>

<script>
function openRenameModal() {
    document.getElementById('renameModal').classList.add('active');
}

function closeRenameModal() {
    document.getElementById('renameModal').classList.remove('active');
}

function openLightbox(imageSrc) {
    const modal = document.getElementById('lightboxModal');
    const modalImg = document.getElementById('lightboxImg');
    modalImg.src = imageSrc;
    modal.classList.add('active');
}

function closeLightbox(event) {
    if (event.target.id === 'lightboxModal' || event.target.classList.contains('lightbox-close')) {
        const modal = document.getElementById('lightboxModal');
        modal.classList.remove('active');
    }
}
</script>

</body>
</html>