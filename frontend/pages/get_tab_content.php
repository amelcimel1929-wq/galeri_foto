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
    .grid-photos {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 16px;
        margin-top: 20px;
    }
    .grid-item {
        background: #f0f0f0;
        border-radius: 16px;
        overflow: hidden;
        position: relative;
    }
    .grid-item img {
        width: 100%;
        height: 250px;
        object-fit: cover;
        display: block;
        transition: transform 0.2s ease;
    }
    .grid-item:hover img {
        transform: scale(1.03);
    }
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
    .board-cover {
        width: 100%;
        height: 180px;
        background-color: #e9e9e9;
        border-radius: 16px;
        overflow: hidden;
        position: relative;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        transition: transform 0.2s ease;
    }
    .board-card-container:hover .board-cover {
        transform: translateY(-4px);
    }
    .board-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .board-empty-cover {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #8e8e8e;
        font-size: 2rem;
    }
    .board-info { padding: 8px 4px; }
    .board-title { font-weight: 700; font-size: 1rem; margin: 4px 0 2px 0; }
    .board-count { font-size: 0.85rem; color: #767676; margin: 0; }
    .empty-state-text { color: #767676; grid-column: 1/-1; padding: 20px 0; }
</style>

<?php if (!$id_user): ?>
    <p class="empty-state-text">Silakan masuk terlebih dahulu untuk melihat koleksi Anda.</p>
<?php else: ?>

    <?php if ($tab === 'liked'): ?>
        <!-- ================= TAB LIKED ================= -->
        <div class="grid-photos">
            <?php
            // Query JOIN antara like_foto dan foto
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
                    <div class="grid-item">
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
        <!-- ================= TAB BOARDS ================= -->
        <div class="boards-grid">
            <?php
            // Query Album milik User beserta jumlah Pin dan Sampul Foto
            $qAlbum = "SELECT a.*, 
                        (SELECT COUNT(*) FROM save_foto sf WHERE sf.id_album = a.id_album) as total_foto,
                        (SELECT f.lokasi_file FROM save_foto sf JOIN foto f ON sf.id_foto = f.id_foto WHERE sf.id_album = a.id_album ORDER BY sf.tanggal_simpan DESC LIMIT 1) as foto_sampul
                       FROM album a 
                       WHERE a.id_user = ? 
                       ORDER BY a.tanggal_dibuat DESC";

            $stmtA = $koneksi->prepare($qAlbum);
            $stmtA->bind_param("i", $id_user);
            $stmtA->execute();
            $resAlbum = $stmtA->get_result();

            if ($resAlbum->num_rows > 0):
                while ($album = $resAlbum->fetch_assoc()):
            ?>
                    <a href="detail_board.php?id_album=<?= $album['id_album']; ?>" class="board-card-container">
                        <div class="board-cover">
                            <?php if (!empty($album['foto_sampul'])): ?>
                                <img src="<?= $upload_path . htmlspecialchars($album['foto_sampul']); ?>" alt="Sampul Board">
                            <?php else: ?>
                                <div class="board-empty-cover">
                                    <i class="fa-regular fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="board-info">
                            <h3 class="board-title"><?= htmlspecialchars($album['nama_album']); ?></h3>
                            <p class="board-count"><?= $album['total_foto']; ?> Pin</p>
                        </div>
                    </a>
            <?php 
                endwhile;
            else:
                // FALLBACK: JIKA BELUM PUNYA ALBUM, TAMPILKAN FOTO YANG DIUNGGAH USER
                $qFotoUser = "SELECT * FROM foto WHERE id_user = ? ORDER BY id_foto DESC";
                $stmtFU = $koneksi->prepare($qFotoUser);
                $stmtFU->bind_param("i", $id_user);
                $stmtFU->execute();
                $resFU = $stmtFU->get_result();

                if ($resFU->num_rows > 0):
                ?>
                    <div class="grid-photos" style="grid-column: 1/-1;">
                        <?php while ($fu = $resFU->fetch_assoc()): ?>
                            <div class="grid-item">
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
        </div>

    <?php else: ?>
        <!-- ================= TAB COLLAGES ================= -->
        <div style="margin-top: 20px;">
            <p class="empty-state-text">Belum ada collage.</p>
        </div>
    <?php endif; ?>

<?php endif; ?>