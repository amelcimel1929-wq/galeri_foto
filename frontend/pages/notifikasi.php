<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($koneksi)) {
    include_once __DIR__ . '/../../backend/config/connection.php';
}

$id_user_login = $_SESSION['id_user'] ?? 0;

// Menggunakan LEFT JOIN agar notifikasi tipe 'follow' (yang id_foto-nya NULL) tetap terbaca
$query = "SELECT n.*, 
                 u.username AS nama_pemicu, 
                 u.foto_profil AS foto_pemicu, 
                 f.lokasi_file AS foto_post
          FROM notifikasi n
          JOIN user u ON n.id_user_pemicu = u.id_user
          LEFT JOIN foto f ON n.id_foto = f.id_foto
          WHERE n.id_user_penerima = ?
          ORDER BY n.tanggal_notifikasi DESC";

$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $id_user_login);
$stmt->execute();
$result = $stmt->get_result();

function formatWaktuAwal($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'Baru saja';
    if ($diff < 3600) return floor($diff / 60) . ' m';
    if ($diff < 86400) return floor($diff / 3600) . ' h';
    if ($diff < 2592000) return floor($diff / 86400) . ' d';
    return floor($diff / 2592000) . ' mo';
}

function cariFotoUrl($namaFile) {
    if (empty($namaFile)) return null;
    if (file_exists(__DIR__ . '/../../backend/uploads/' . $namaFile)) {
        return '/galeri_foto/backend/uploads/' . htmlspecialchars($namaFile);
    } elseif (file_exists(__DIR__ . '/../../uploads/' . $namaFile)) {
        return '/galeri_foto/uploads/' . htmlspecialchars($namaFile);
    }
    return null;
}

// ==== KELOMPOKKAN NOTIFIKASI SEPERTI TIKTOK (Baru / Minggu ini / Lebih lama) ====
$grup_notifikasi = [
    'baru'      => [],
    'minggu'    => [],
    'lama'      => [],
];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $waktu = strtotime($row['tanggal_notifikasi']);
        $selisih = time() - $waktu;

        if ($selisih < 86400) {
            $grup_notifikasi['baru'][] = $row;
        } elseif ($selisih < (86400 * 7)) {
            $grup_notifikasi['minggu'][] = $row;
        } else {
            $grup_notifikasi['lama'][] = $row;
        }
    }
}

$label_grup = [
    'baru'   => 'Baru',
    'minggu' => 'Minggu ini',
    'lama'   => 'Lebih lama',
];

// Icon per tipe notifikasi (badge kecil di pojok avatar, gaya TikTok)
function iconTipeNotif($tipe) {
    switch ($tipe) {
        case 'like':
            return ['icon' => 'fa-heart', 'bg' => '#ff2d55'];
        case 'komentar':
            return ['icon' => 'fa-comment', 'bg' => '#25d366'];
        case 'favorit':
            return ['icon' => 'fa-star', 'bg' => '#ffb800'];
        case 'follow':
            return ['icon' => 'fa-user-plus', 'bg' => '#e60023'];
        default:
            return ['icon' => 'fa-bell', 'bg' => '#767676'];
    }
}

$ada_notifikasi = !empty($grup_notifikasi['baru']) || !empty($grup_notifikasi['minggu']) || !empty($grup_notifikasi['lama']);
?>

<style>
/* ==== NOTIFICATION PANEL - GAYA TIKTOK ==== */
.notif-section-label {
    font-size: 15px;
    font-weight: 700;
    color: #111;
    margin: 18px 0 10px 0;
    padding: 0 4px;
}

.notif-section-label:first-of-type {
    margin-top: 4px;
}

.notif-list {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.notif-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 6px;
    border-radius: 14px;
    text-decoration: none;
    color: inherit;
    transition: background-color 0.15s;
    position: relative;
}

.notif-item:hover {
    background-color: #f5f5f5;
}

/* Avatar pemicu (kiri) dengan badge tipe aksi ala TikTok */
.notif-avatar-wrapper {
    position: relative;
    flex-shrink: 0;
    width: 48px;
    height: 48px;
}

.notif-img-box {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    background-color: #e9e9e9;
}

.notif-img-box.notif-thumb-post {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    margin-left: auto;
}

.notif-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.notif-avatar-fallback {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #7bdcb5;
    font-weight: 700;
    color: #111;
    font-size: 18px;
}

.notif-type-badge {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 10px;
    border: 2px solid #ffffff;
}

.notif-content {
    flex: 1;
    min-width: 0;
}

.notif-text {
    font-size: 14px;
    color: #111;
    line-height: 1.35;
    margin: 0;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.notif-text strong {
    font-weight: 700;
}

.notif-time {
    font-size: 12px;
    color: #a3a3a3;
    margin-top: 3px;
    display: block;
}

/* Tombol follow-back di kanan, khusus tipe follow */
.notif-follow-btn {
    flex-shrink: 0;
    background-color: #111;
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    padding: 8px 16px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    white-space: nowrap;
}

.notif-follow-btn:hover {
    background-color: #333;
}

/* Dot merah unread di kiri item (opsional, tipe 'baru') */
.notif-unread-dot {
    width: 8px;
    height: 8px;
    background-color: #e60023;
    border-radius: 50%;
    flex-shrink: 0;
}

.notif-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    color: #a3a3a3;
    text-align: center;
    gap: 10px;
}

.notif-empty-state i {
    font-size: 32px;
    color: #d0d0d0;
}

.notif-empty-state span {
    font-size: 14px;
}
</style>

<div class="notification-panel" id="notificationPanel">
    <div class="notif-header">
        <h3 class="notif-title">Notifications</h3>
        <button type="button" class="close-btn" id="closeNotifBtn">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <?php if ($ada_notifikasi): ?>

        <?php foreach ($grup_notifikasi as $key_grup => $daftar): ?>
            <?php if (!empty($daftar)): ?>
                <div class="notif-section-label"><?php echo $label_grup[$key_grup]; ?></div>
                <div class="notif-list">
                    <?php foreach ($daftar as $row): ?>
                        <?php 
                            $url_foto_profil = cariFotoUrl($row['foto_pemicu']);
                            $url_foto_post   = cariFotoUrl($row['foto_post']);
                            $badge            = iconTipeNotif($row['tipe']);
                            $is_unread        = empty($row['is_read']);

                            $link_destination = ($row['tipe'] === 'follow') 
                                ? "/galeri_foto/frontend/pages/profile_dimata_userlain.php?user_id=" . $row['id_user_pemicu']
                                : "/galeri_foto/frontend/pages/detail.php?id=" . $row['id_foto'];
                        ?>
                        <a href="<?php echo $link_destination; ?>" class="notif-item">

                            <?php if ($is_unread): ?>
                                <span class="notif-unread-dot"></span>
                            <?php endif; ?>

                            <div class="notif-avatar-wrapper">
                                <div class="notif-img-box">
                                    <?php if ($url_foto_profil): ?>
                                        <img src="<?php echo $url_foto_profil; ?>" alt="Profile" class="notif-img">
                                    <?php else: ?>
                                        <div class="notif-avatar-fallback">
                                            <?php echo strtoupper(substr($row['nama_pemicu'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <span class="notif-type-badge" style="background-color: <?php echo $badge['bg']; ?>;">
                                    <i class="fa-solid <?php echo $badge['icon']; ?>"></i>
                                </span>
                            </div>

                            <div class="notif-content">
                                <p class="notif-text">
                                    <strong><?php echo htmlspecialchars($row['nama_pemicu']); ?></strong> 
                                    <?php echo htmlspecialchars($row['pesan']); ?>
                                </p>
                                <span class="notif-time"><?php echo formatWaktuAwal($row['tanggal_notifikasi']); ?></span>
                            </div>

                            <?php if ($row['tipe'] === 'follow'): ?>
                                <?php
                                $cekIkut = $koneksi->prepare('SELECT 1 FROM follow WHERE id_follower = ? AND id_following = ?');
                                $cekIkut->bind_param('ii', $_SESSION['id_user'], $row['id_user_pemicu']);
                                $cekIkut->execute();
                                $sudahIkut = $cekIkut->get_result()->num_rows > 0;
                                $cekIkut->close();
                                ?>
                                <?php if (!$sudahIkut && (int)$row['id_user_pemicu'] !== (int)$_SESSION['id_user']): ?>
                                    <form action="/galeri_foto/backend/controllers/follow_process.php" method="POST" onclick="event.stopPropagation();">
                                        <input type="hidden" name="id_following" value="<?= (int)$row['id_user_pemicu']; ?>">
                                        <input type="hidden" name="follow_back" value="1">
                                        <button type="submit" class="notif-follow-btn">Follow Back</button>
                                    </form>
                                <?php else: ?><span class="notif-follow-btn">Teman</span><?php endif; ?>
                            <?php elseif ($url_foto_post): ?>
                                <div class="notif-img-box notif-thumb-post">
                                    <img src="<?php echo $url_foto_post; ?>" alt="Post" class="notif-img">
                                </div>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

    <?php else: ?>
        <div class="notif-empty-state">
            <i class="fa-regular fa-bell"></i>
            <span>Belum ada notifikasi.</span>
        </div>
    <?php endif; ?>
</div>
