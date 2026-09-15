<?php 
session_start();

include '../../backend/config/connection.php';

$id_user_login = $_SESSION['id_user'] ?? null;
$tab = $_GET['tab'] ?? 'collages';

$liked_photos = [];
if ($tab === 'liked' && $id_user_login) {
    $sql = "SELECT foto.id_foto, foto.judul_foto, foto.lokasi_file, user.username
            FROM like_foto
            JOIN foto ON like_foto.id_foto = foto.id_foto
            JOIN user ON foto.id_user = user.id_user
            WHERE like_foto.id_user = ?
            ORDER BY like_foto.tanggal_like DESC";

    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_user_login);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $liked_photos[] = $row;
    }
}
?>

<?php if ($tab === 'liked'): ?>

    <div class="masonry-container">
        <?php if (count($liked_photos) > 0): ?>
            <?php foreach ($liked_photos as $foto): ?>
                <div class="pin-card">
                    <img src="../../backend/uploads/<?= htmlspecialchars($foto['lokasi_file']) ?>" alt="<?= htmlspecialchars($foto['judul_foto']) ?>">
                    <div class="pin-overlay">
                        <button class="btn-save">Save</button>
                    </div>
                    <div class="pin-info">
                        <p class="pin-title"><?= htmlspecialchars($foto['judul_foto']) ?></p>
                        <p class="pin-user"><i class="fa-solid fa-user"></i> <?= htmlspecialchars($foto['username']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="empty-state-text">Belum ada foto yang kamu suka.</p>
        <?php endif; ?>
    </div>

<?php elseif ($tab === 'boards'): ?>

    <div class="create-btn-wrapper">
        <button class="btn-create-red">Create</button>
    </div>
    <p class="empty-state-text">Kamu belum punya board.</p>

<?php else: ?>

    <div class="create-btn-wrapper">
        <button class="btn-create-red">Create</button>
    </div>
    <div class="drafts-section">
        <h3>Drafts (0)</h3>
    </div>

<?php endif; ?>