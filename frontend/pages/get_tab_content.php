<?php
// Cek dan jalankan session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Koneksi ke Database
require_once __DIR__ . '/../../backend/config/connection.php';

// Ambil ID User, Tab Aktif, dan Filter Privacy
$id_user = $_SESSION['id_user'] ?? $_GET['id_user'] ?? 0;
$tab     = $_GET['tab'] ?? 'boards';
$privacy = $_GET['privacy'] ?? 'all';

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
        transition: transform 0.2s ease;
    }

    .grid-item:hover {
        transform: scale(1.02);
    }

    .grid-item a {
        display: block;
        width: 100%;
        height: 100%;
        text-decoration: none;
    }

    .grid-item img {
        width: 100%;
        height: auto;
        display: block;
        border-radius: 16px;
        object-fit: cover;
    }

    /* Badge penanda Private jika foto diset Private */
    .private-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(0, 0, 0, 0.65);
        color: #fff;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        backdrop-filter: blur(4px);
    }

    /* ================= 2. BOARDS & COLLAGES GRID ================= */
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
                    <div class="grid-item">
                        <a href="detail.php?id=<?= $row['id_foto']; ?>">
                            <img src="<?= $upload_path . htmlspecialchars($row['lokasi_file']); ?>" 
                                 alt="<?= htmlspecialchars($row['judul_foto'] ?? 'Liked Foto'); ?>">
                        </a>
                    </div>
            <?php 
                endwhile;
            else:
                echo "<p class='empty-state-text'>Belum ada foto yang kamu sukai.</p>";
            endif;
            $stmt->close();
            ?>
        </div>

    <?php elseif ($tab === 'collages'): ?>
        <!-- ================= TAB COLLAGES (ALBUM FAVORIT) ================= -->
        <?php
        $qAlbumFav = "SELECT af.*, 
                      (SELECT COUNT(*) FROM favorit fv WHERE fv.id_album_favorit = af.id_album_favorit) as total_foto
                      FROM album_favorit af 
                      WHERE af.id_user = ? 
                      ORDER BY af.id_album_favorit DESC";

        $stmtAF = $koneksi->prepare($qAlbumFav);
        $stmtAF->bind_param("i", $id_user);
        $stmtAF->execute();
        $resAlbumFav = $stmtAF->get_result();

        if ($resAlbumFav->num_rows > 0):
        ?>
            <div class="boards-grid">
                <?php while ($album = $resAlbumFav->fetch_assoc()): 
                    $q4FotoFav = "SELECT f.lokasi_file 
                                  FROM favorit fv 
                                  JOIN foto f ON fv.id_foto = f.id_foto 
                                  WHERE fv.id_album_favorit = ? 
                                  ORDER BY fv.id_favorit DESC LIMIT 4";
                    $stmt4F = $koneksi->prepare($q4FotoFav);
                    $stmt4F->bind_param("i", $album['id_album_favorit']);
                    $stmt4F->execute();
                    $res4FotoFav = $stmt4F->get_result();

                    $fotos = [];
                    while ($f = $res4FotoFav->fetch_assoc()) {
                        $fotos[] = $f['lokasi_file'];
                    }
                    $stmt4F->close();
                ?>
                    <a href="detail_collage.php?id_album_favorit=<?= $album['id_album_favorit']; ?>" class="board-card-container">
                        <div class="board-cover-grid">
                            <?php 
                            for ($i = 0; $i < 4; $i++): 
                                if (isset($fotos[$i])): 
                            ?>
                                    <img src="<?= $upload_path . htmlspecialchars($fotos[$i]); ?>" alt="Preview Album Favorit">
                            <?php else: ?>
                                    <div class="empty-slot"></div>
                            <?php 
                                endif;
                            endfor; 
                            ?>
                        </div>
                        <div class="board-info">
                            <h3 class="board-title">📁 <?= htmlspecialchars($album['nama_album']); ?></h3>
                            <p class="board-count"><?= $album['total_foto']; ?> Favorit</p>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php 
        else:
            echo "<p class='empty-state-text'>Belum ada album collage/favorit yang dibuat.</p>";
        endif;
        $stmtAF->close();
        ?>

    <?php elseif ($tab === 'boards'): ?>
        <!-- ================= TAB BOARDS / FOTO (BERDASARKAN FILTER PRIVASI) ================= -->
        <?php
        // 1. Ambil album milik user
        $qAlbum = "SELECT a.*, 
                    (SELECT COUNT(*) FROM save_foto sf WHERE sf.id_album = a.id_album) as total_foto
                   FROM album a 
                   WHERE a.id_user = ? 
                   ORDER BY a.tanggal_dibuat DESC";

        $stmtA = $koneksi->prepare($qAlbum);
        $stmtA->bind_param("i", $id_user);
        $stmtA->execute();
        $resAlbum = $stmtA->get_result();

        // Jika user punya album, tampilkan daftar albumnya
        if ($resAlbum->num_rows > 0 && $privacy === 'all'):
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
            // 2. Tampilkan Foto Unggahan berdasarkan Sub-tab Privasi (Semua, Public, atau Private)
            if ($privacy === 'public') {
                $qFotoUser = "SELECT * FROM foto WHERE id_user = ? AND visibilitas = 'public' ORDER BY id_foto DESC";
            } elseif ($privacy === 'private') {
                $qFotoUser = "SELECT * FROM foto WHERE id_user = ? AND visibilitas = 'private' ORDER BY id_foto DESC";
            } else { // privacy === 'all'
                $qFotoUser = "SELECT * FROM foto WHERE id_user = ? ORDER BY id_foto DESC";
            }

            $stmtFU = $koneksi->prepare($qFotoUser);
            $stmtFU->bind_param("i", $id_user);
            $stmtFU->execute();
            $resFU = $stmtFU->get_result();

            if ($resFU->num_rows > 0):
            ?>
                <div class="grid-photos">
                    <?php while ($fu = $resFU->fetch_assoc()): ?>
                        <div class="grid-item">
                            <a href="detail.php?id=<?= $fu['id_foto']; ?>">
                                <img src="<?= $upload_path . htmlspecialchars($fu['lokasi_file']); ?>" alt="Foto User">
                                <?php if ($fu['visibilitas'] === 'private'): ?>
                                    <span class="private-badge"><i class="fa-solid fa-lock"></i> Private</span>
                                <?php endif; ?>
                            </a>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php
            else:
                if ($privacy === 'private') {
                    echo "<p class='empty-state-text'>Belum ada foto yang diarsipkan/diprivat.</p>";
                } elseif ($privacy === 'public') {
                    echo "<p class='empty-state-text'>Belum ada foto publik.</p>";
                } else {
                    echo "<p class='empty-state-text'>Kamu belum punya board atau foto.</p>";
                }
            endif;
            $stmtFU->close();
        endif;
        $stmtA->close();
        ?>

    <?php endif; ?>

<?php endif; ?>