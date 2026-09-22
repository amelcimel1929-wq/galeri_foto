<?php
session_start();

if (!isset($_SESSION['id_user'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../../backend/config/connection.php';
include '../partials/header.php';
include '../partials/navbar.php';

$id_album_favorit = isset($_GET['id_album_favorit']) ? intval($_GET['id_album_favorit']) : 0;
$id_user_login = $_SESSION['id_user'];

// Ambil data album favorit
$qAlbum = "SELECT * FROM album_favorit WHERE id_album_favorit = ? AND id_user = ?";
$stmtA = $koneksi->prepare($qAlbum);
$stmtA->bind_param("ii", $id_album_favorit, $id_user_login);
$stmtA->execute();
$album = $stmtA->get_result()->fetch_assoc();

if (!$album) {
    echo "<p style='margin-top:100px; text-align:center;'>Album favorit tidak ditemukan.</p>";
    exit;
}

$upload_path = "../../backend/uploads/";
?>

<!-- Import Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    body {
        background-color: #ffffff;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        padding-top: 80px;
    }

    .collage-detail-container {
        max-width: 1200px;
        margin: 0 auto;
        margin-left: calc(50% - 750px);
        padding: 0 16px;
        position: relative;
    }

    .btn-back {
        position: absolute;
        top: 0;
        left: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        background-color: #efefef;
        border-radius: 50%;
        color: #111;
        text-decoration: none;
        transition: background-color 0.2s ease, transform 0.2s ease;
        z-index: 10;
    }

    .btn-back:hover {
        background-color: #e2e2e2;
        transform: scale(1.05);
    }

    .collage-header {
        text-align: center;
        margin-bottom: 30px;
    }

    /* Layout khusus judul & tombol aksi */
    .collage-title-wrapper {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .collage-title {
        font-size: 28px;
        font-weight: 700;
        color: #111;
        margin: 0;
    }

    .btn-action-icon {
        background: #efefef;
        border: none;
        width: 36px;
        height: 36px;
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

    .collage-subtitle {
        color: #767676;
        font-size: 14px;
        margin-top: 6px;
    }

    /* Grid Foto (Masonry) */
    .grid-photos {
        column-count: 5;
        column-gap: 16px;
        width: 100%;
    }

    .grid-item {
        break-inside: avoid;
        margin-bottom: 16px;
        background: #f0f0f0;
        border-radius: 16px;
        overflow: hidden;
        position: relative;
        cursor: pointer;
        transition: transform 0.2s ease;
    }

    .grid-item:hover {
        transform: scale(1.02);
    }

    .grid-item img {
        width: 100%;
        height: auto;
        display: block;
        border-radius: 16px;
        object-fit: cover;
    }

    .empty-state {
        text-align: center;
        color: #767676;
        margin-top: 40px;
    }

    /* Modal Rename Album */
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

    .edit-modal-overlay.active {
        display: flex;
    }

    .edit-modal-box {
        background: #ffffff;
        padding: 24px;
        border-radius: 16px;
        width: 100%;
        max-width: 380px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .edit-modal-box h3 {
        margin: 0 0 16px 0;
        font-size: 18px;
        color: #111;
    }

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

    .edit-modal-box input[type="text"]:focus {
        border-color: #000;
    }

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

    @media (max-width: 1200px) { .grid-photos { column-count: 4; } }
    @media (max-width: 900px)  { .grid-photos { column-count: 3; } }
    @media (max-width: 600px)  { 
        .grid-photos { column-count: 2; } 
        .btn-back { top: -10px; left: 10px; }
    }
</style>

<div class="collage-detail-container">
    <a href="profile.php?tab=collages" class="btn-back" title="Kembali ke Profile">
        <i class="fa-solid fa-arrow-left"></i>
    </a>

    <div class="collage-header">
        <div class="collage-title-wrapper">
            <h1 class="collage-title">📁 <?= htmlspecialchars($album['nama_album']); ?></h1>
            
            <!-- Icon Edit Nama Album -->
            <button type="button" class="btn-action-icon" onclick="openRenameModal()" title="Ubah Nama Album">
                <i class="fa-solid fa-pen"></i>
            </button>
            
            <!-- Icon Hapus Album -->
            <a href="../../backend/controllers/process_delete_collage.php?id_album_favorit=<?= $album['id_album_favorit']; ?>" 
               class="btn-action-icon delete" 
               title="Hapus Album"
               onclick="return confirm('Apakah Anda yakin ingin menghapus album ini? Semua item favorit di dalamnya akan dihapus.')">
                <i class="fa-solid fa-trash"></i>
            </a>
        </div>
        <p class="collage-subtitle">Album Favorit / Collage</p>
    </div>

    <div class="grid-photos">
        <?php
        $qFotos = "SELECT f.* 
                   FROM favorit fv 
                   JOIN foto f ON fv.id_foto = f.id_foto 
                   WHERE fv.id_album_favorit = ? 
                   ORDER BY fv.id_favorit DESC";

        $stmtF = $koneksi->prepare($qFotos);
        $stmtF->bind_param("i", $id_album_favorit);
        $stmtF->execute();
        $resFotos = $stmtF->get_result();

        if ($resFotos->num_rows > 0):
            while ($foto = $resFotos->fetch_assoc()):
        ?>
                <div class="grid-item" onclick="window.location.href='detail.php?id=<?= $foto['id_foto']; ?>'">
                    <img src="<?= $upload_path . htmlspecialchars($foto['lokasi_file']); ?>" 
                         alt="<?= htmlspecialchars($foto['judul_foto']); ?>">
                </div>
        <?php 
            endwhile;
        else:
            echo "<p class='empty-state'>Belum ada foto favorit di album ini.</p>";
        endif;
        $stmtF->close();
        ?>
    </div>
</div>

<!-- Modal Rename Album -->
<div id="renameModal" class="edit-modal-overlay">
    <div class="edit-modal-box">
        <h3>Ubah Nama Album</h3>
        <form action="../../backend/controllers/process_update_collage.php" method="POST">
            <input type="hidden" name="id_album_favorit" value="<?= $album['id_album_favorit']; ?>">
            <input type="text" name="nama_album" value="<?= htmlspecialchars($album['nama_album']); ?>" required>
            <div class="edit-modal-actions">
                <button type="button" class="btn-modal btn-modal-cancel" onclick="closeRenameModal()">Batal</button>
                <button type="submit" class="btn-modal btn-modal-save">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRenameModal() {
        document.getElementById('renameModal').classList.add('active');
    }

    function closeRenameModal() {
        document.getElementById('renameModal').classList.remove('active');
    }
</script>

</body>
</html>