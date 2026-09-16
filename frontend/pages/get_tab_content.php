<?php
// Cek dan jalankan session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Koneksi ke Database
require_once __DIR__ . '/../../backend/config/connection.php';

// Ambil ID User dan Tab Aktif
$id_user = $_SESSION['id_user'] ?? $_GET['id_user'] ?? 0;
$tab     = $_GET['tab'] ?? 'boards';

// Base Path folder upload gambar
$upload_path = "../../backend/uploads/"; 
?>

<style>
    /* ================= 1. MASONRY GRID UNTUK FOTO ================= */
    .grid-photos {
        column-count: 5;
        column-gap: 16px;
        width: 100%;
        margin-top: 20px;
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

    /* ================= 2. BOARDS GRID ================= */
    .boards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .board-card-container {
        text-decoration: none;
        color: #111;
        display: block;
    }

    .board-cover-grid {
        width: 100%;
        height: 200px;
        background-color: #e9e9e9;
        border-radius: 16px;
        overflow: hidden;
        display: grid;
        grid-template-columns: 1fr 1fr;
        grid-template-rows: 1fr 1fr;
        gap: 2px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        transition: transform 0.2s ease;
    }

    .board-card-container:hover .board-cover-grid {
        transform: translateY(-4px);
    }

    .board-cover-grid img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .empty-slot { background-color: #e0e0e0; }
    .board-info { padding: 8px 4px; }
    .board-title { font-weight: 700; font-size: 1rem; margin: 4px 0 2px 0; }
    .board-count { font-size: 0.85rem; color: #767676; margin: 0; }
    .empty-state-text { color: #767676; grid-column: 1/-1; padding: 20px 0; text-align: center; }

    /* ================= 3. MODAL POP-UP IMAGE ================= */
    .image-modal-overlay {
        display: none; /* Tersembunyi secara default */
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.75);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        backdrop-filter: blur(4px);
    }

    .image-modal-overlay.active {
        display: flex;
    }

    .image-modal-content {
        position: relative;
        max-width: 80vw;
        max-height: 85vh;
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }

    .image-modal-content img {
        max-width: 80vw;
        max-height: 85vh;
        object-fit: contain;
        display: block;
    }

    .image-modal-close {
        position: absolute;
        top: 15px;
        right: 20px;
        color: #fff;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        z-index: 10000;
        text-shadow: 0 2px 5px rgba(0,0,0,0.5);
    }

    @media (max-width: 1200px) { .grid-photos { column-count: 4; } }
    @media (max-width: 900px)  { .grid-photos { column-count: 3; } }
    @media (max-width: 600px)  { .grid-photos { column-count: 2; } }
</style>

<?php if (!$id_user): ?>
    <p class="empty-state-text">Silakan masuk terlebih dahulu untuk melihat koleksi Anda.</p>
<?php else: ?>

    <?php if ($tab === 'liked'): ?>
        <!-- ================= TAB LIKED ================= -->
        <div class="grid-photos">
            <?php
            $qLiked = "SELECT f.* 
                       FROM like_foto l 
                       JOIN foto f ON l.id_foto = f.id_foto 
                       WHERE l.id_user = ? 
                       ORDER BY l.tanggal_like DESC";

            $stmt = $koneksi->prepare($qLiked);
            $stmt->bind_param("i", $id_user);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows > 0):
                while ($row = $res->fetch_assoc()):
            ?>
                    <div class="grid-item" onclick="openModal('<?= $upload_path . htmlspecialchars($row['lokasi_file']); ?>')">
                        <img src="<?= $upload_path . htmlspecialchars($row['lokasi_file']); ?>" 
                             alt="<?= htmlspecialchars($row['judul_foto'] ?? 'Liked Foto'); ?>">
                    </div>
            <?php 
                endwhile;
            else:
                echo "<p class='empty-state-text'>Belum ada foto yang kamu sukai.</p>";
            endif;
            $stmt->close();
            ?>
        </div>

    <?php elseif ($tab === 'boards'): ?>
        <!-- ================= TAB BOARDS (GRID COVER 2x2) ================= -->
        <?php
        $qAlbum = "SELECT a.*, 
                    (SELECT COUNT(*) FROM save_foto sf WHERE sf.id_album = a.id_album) as total_foto
                   FROM album a 
                   WHERE a.id_user = ? 
                   ORDER BY a.tanggal_dibuat DESC";

        $stmtA = $koneksi->prepare($qAlbum);
        $stmtA->bind_param("i", $id_user);
        $stmtA->execute();
        $resAlbum = $stmtA->get_result();

        if ($resAlbum->num_rows > 0):
        ?>
            <div class="boards-grid">
                <?php while ($album = $resAlbum->fetch_assoc()): 
                    $q4Foto = "SELECT f.lokasi_file 
                               FROM save_foto sf 
                               JOIN foto f ON sf.id_foto = f.id_foto 
                               WHERE sf.id_album = ? 
                               ORDER BY sf.tanggal_simpan DESC LIMIT 4";
                    $stmt4 = $koneksi->prepare($q4Foto);
                    $stmt4->bind_param("i", $album['id_album']);
                    $stmt4->execute();
                    $res4Foto = $stmt4->get_result();

                    $fotos = [];
                    while ($f = $res4Foto->fetch_assoc()) {
                        $fotos[] = $f['lokasi_file'];
                    }
                    $stmt4->close();
                ?>
                    <a href="detail_board.php?id_album=<?= $album['id_album']; ?>" class="board-card-container">
                        <div class="board-cover-grid">
                            <?php 
                            for ($i = 0; $i < 4; $i++): 
                                if (isset($fotos[$i])): 
                            ?>
                                    <img src="<?= $upload_path . htmlspecialchars($fotos[$i]); ?>" alt="Preview Album">
                            <?php else: ?>
                                    <div class="empty-slot"></div>
                            <?php 
                                endif;
                            endfor; 
                            ?>
                        </div>
                        <div class="board-info">
                            <h3 class="board-title"><?= htmlspecialchars($album['nama_album']); ?></h3>
                            <p class="board-count"><?= $album['total_foto']; ?> Pin</p>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php 
        else:
            // FALLBACK FOTO UNGGAHAN USER
            $qFotoUser = "SELECT * FROM foto WHERE id_user = ? ORDER BY id_foto DESC";
            $stmtFU = $koneksi->prepare($qFotoUser);
            $stmtFU->bind_param("i", $id_user);
            $stmtFU->execute();
            $resFU = $stmtFU->get_result();

            if ($resFU->num_rows > 0):
            ?>
                <div class="grid-photos">
                    <?php while ($fu = $resFU->fetch_assoc()): ?>
                        <div class="grid-item" onclick="openModal('<?= $upload_path . htmlspecialchars($fu['lokasi_file']); ?>')">
                            <img src="<?= $upload_path . htmlspecialchars($fu['lokasi_file']); ?>" alt="Foto User">
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php
            else:
                echo "<p class='empty-state-text'>Kamu belum punya board atau foto.</p>";
            endif;
            $stmtFU->close();
        endif;
        $stmtA->close();
        ?>

    <?php else: ?>
        <div style="margin-top: 20px;">
            <p class="empty-state-text">Belum ada collage.</p>
        </div>
    <?php endif; ?>

<?php endif; ?>

<!-- HTML STRUCT POPUP MODAL -->
<div id="imageModal" class="image-modal-overlay" onclick="closeModal(event)">
    <span class="image-modal-close" onclick="forceCloseModal()">&times;</span>
    <div class="image-modal-content" onclick="event.stopPropagation()">
        <img id="modalImg" src="" alt="Preview Gambar">
    </div>
</div>

<script>
    function openModal(imageSrc) {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImg');
        modalImg.src = imageSrc;
        modal.classList.add('active');
    }

    function closeModal(event) {
        if (event.target.id === 'imageModal') {
            document.getElementById('imageModal').classList.remove('active');
        }
    }

    function forceCloseModal() {
        document.getElementById('imageModal').classList.remove('active');
    }
</script>