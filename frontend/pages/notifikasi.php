<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Koneksi ke database
if (!isset($koneksi)) {
    include_once __DIR__ . '/../../backend/config/connection.php';
}

$id_user_login = $_SESSION['id_user'] ?? 0;

// Query mengambil data notifikasi beserta info user pemicu & foto
$query = "SELECT n.*, 
                 u.username AS nama_pemicu, 
                 u.foto_profil AS foto_pemicu, 
                 f.lokasi_file AS foto_post
          FROM notifikasi n
          JOIN user u ON n.id_user_pemicu = u.id_user
          JOIN foto f ON n.id_foto = f.id_foto
          WHERE n.id_user_penerima = ?
          ORDER BY n.tanggal_notifikasi DESC";

$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $id_user_login);
$stmt->execute();
$result = $stmt->get_result();

// Format waktu relatif
function formatWaktuAwal($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'Baru saja';
    if ($diff < 3600) return floor($diff / 60) . ' m';
    if ($diff < 86400) return floor($diff / 3600) . ' h';
    if ($diff < 2592000) return floor($diff / 86400) . ' d';
    return floor($diff / 2592000) . ' mo';
}

// FIX: Cari file foto di 2 kemungkinan folder (backend/uploads = lokasi baku, uploads = folder lama)
// Return null kalau file gak ketemu di manapun (biar gampang kasih fallback tanpa hang ke internet)
function cariFotoUrl($namaFile) {
    if (empty($namaFile)) return null;
    if (file_exists(__DIR__ . '/../../backend/uploads/' . $namaFile)) {
        return '/galeri_foto/backend/uploads/' . htmlspecialchars($namaFile);
    } elseif (file_exists(__DIR__ . '/../../uploads/' . $namaFile)) {
        return '/galeri_foto/uploads/' . htmlspecialchars($namaFile);
    }
    return null;
}
?>

<!-- Wadah Panel Notifikasi (Menggunakan class notification-panel agar sinkron dengan navbar.php) -->
<div class="notification-panel" id="notificationPanel">
    <div class="notif-header">
        <h3 class="notif-title">Notifications</h3>
        <button type="button" class="close-btn" id="closeNotifBtn">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="notif-section-label">Updates</div>

    <div class="notif-list">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php 
                    // FIX: cari URL foto profil pemicu, null kalau tidak ketemu di kedua folder
                    $url_foto_profil = cariFotoUrl($row['foto_pemicu']);
                    $url_foto_post   = cariFotoUrl($row['foto_post']);
                ?>
                <a href="/galeri_foto/frontend/pages/detail.php?id=<?php echo $row['id_foto']; ?>" class="notif-item">
                    <!-- Foto Profil Pengirim / Pemicu Notif -->
                    <div class="notif-img-box">
                        <?php if ($url_foto_profil): ?>
                            <img src="<?php echo $url_foto_profil; ?>" alt="Profile" class="notif-img">
                        <?php else: ?>
                            <div class="notif-img" style="display:flex; align-items:center; justify-content:center; background:#7bdcb5; font-weight:700; color:#111;">
                                <?php echo strtoupper(substr($row['nama_pemicu'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Isi Pesan Notifikasi -->
                    <div class="notif-content">
                        <p class="notif-text">
                            <strong><?php echo htmlspecialchars($row['nama_pemicu']); ?></strong> 
                            <?php echo htmlspecialchars($row['pesan']); ?>
                        </p>
                        <span class="notif-time"><?php echo formatWaktuAwal($row['tanggal_notifikasi']); ?></span>
                    </div>

                    <!-- Thumbnail Posting Foto -->
                    <div class="notif-img-box">
                        <?php if ($url_foto_post): ?>
                            <img src="<?php echo $url_foto_post; ?>" alt="Post" class="notif-img">
                        <?php else: ?>
                            <div class="notif-img" style="background:#e0e0e0;"></div>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endwhile; ?>
        <?php else: ?>
            <!-- Tampilan jika belum ada notifikasi di database -->
            <div class="notif-item" style="justify-content: center; padding: 20px 0; color: #767676;">
                <span>Belum ada notifikasi.</span>
            </div>
        <?php endif; ?>
    </div>
</div>