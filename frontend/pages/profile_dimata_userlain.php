<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Koneksi ke Database
require_once __DIR__ . '/../../backend/config/connection.php';

// Ambil ID User Target dari Param URL
$user_id_target = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$id_user_login  = $_SESSION['id_user'] ?? 0;

if ($user_id_target <= 0) {
    echo "<script>alert('Pengguna tidak ditemukan!'); window.location.href='index.php';</script>";
    exit();
}

// Fetch Data User Target
$qUser = "SELECT * FROM user WHERE id_user = ?";
$stmtUser = $koneksi->prepare($qUser);
$stmtUser->bind_param("i", $user_id_target);
$stmtUser->execute();
$resUser = $stmtUser->get_result();

if ($resUser->num_rows === 0) {
    echo "<p class='text-center mt-5'>Pengguna tidak ditemukan.</p>";
    exit();
}

$userData = $resUser->fetch_assoc();
$stmtUser->close();

$nama_user     = !empty($userData['nama_lengkap']) ? $userData['nama_lengkap'] : $userData['username'];
$username_user = $userData['username'];
$bio_user      = $userData['bio'] ?? '';
$foto_profil   = $userData['foto_profil'] ?? '';
$inisial       = strtoupper(substr($nama_user, 0, 1));

// -------------------------------------------------------------
// CEK STATUS FOLLOW (Apakah User Login Sudah Follow User Target?)
// -------------------------------------------------------------
$is_following = false;
if ($id_user_login > 0 && $user_id_target > 0) {
    $qCheckFollow = "SELECT 1 FROM follow WHERE id_follower = ? AND id_following = ?";
    $stmtCheck = $koneksi->prepare($qCheckFollow);
    $stmtCheck->bind_param("ii", $id_user_login, $user_id_target);
    $stmtCheck->execute();
    $is_following = $stmtCheck->get_result()->num_rows > 0;
    $stmtCheck->close();
}

// -------------------------------------------------------------
// PENANGANAN PATH UPLOAD & FOTO PROFIL
// -------------------------------------------------------------
$sys_upload_dir = __DIR__ . '/../../backend/uploads/'; // Path absolut server untuk file_exists
$upload_path    = '../../backend/uploads/';            // Path URL relatif untuk HTML img src

// Cek keberadaan foto profil (jika tidak ada di backend/uploads, coba alternatif folder uploads di root)
if (!empty($foto_profil) && file_exists($sys_upload_dir . $foto_profil)) {
    $profile_pic_src = $upload_path . htmlspecialchars($foto_profil);
} elseif (!empty($foto_profil) && file_exists(__DIR__ . '/../../uploads/' . $foto_profil)) {
    $profile_pic_src = '../../uploads/' . htmlspecialchars($foto_profil);
} else {
    $profile_pic_src = null;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($nama_user); ?> (@<?= htmlspecialchars($username_user); ?>)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
            background-color: #fff; 
            color: #111; 
        }
        
        /* Layout Header Profile */
        .profile-header-container {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            justify-content: space-between; /* Menempatkan ikon kembali di sisi kanan berlawanan */
            gap: 20px;
            margin-top: 16px;
            text-align: left;
        }

        .profile-avatar { 
            width: 100px; 
            height: 100px; 
            border-radius: 50%; 
            background-color: #72cb96; 
            color: #111; 
            font-size: 42px; 
            font-weight: 600; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            overflow: hidden; 
            flex-shrink: 0;
        }
        .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
        
        .profile-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: center;
        }

        .profile-title-row {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 12px;
            flex-wrap: wrap;
        }

        .profile-action-row {
            display: flex;
            gap: 10px;
            margin-top: 14px;
        }

        .profile-name { font-size: 24px; font-weight: 700; margin-bottom: 0; line-height: 1.2; }
        .profile-username { color: #5f5f5f; font-size: 14px; margin-top: 2px; margin-bottom: 0; }
        
        .btn-custom-gray { background-color: #e9e9e9; color: #111; font-weight: 600; border-radius: 24px; padding: 6px 16px; border: none; font-size: 14px; }
        .btn-custom-red { background-color: #e60023; color: #fff; font-weight: 600; border-radius: 24px; padding: 6px 16px; border: none; font-size: 14px; }
        
        /* Style Tombol Kembali */
        .btn-back-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e9e9e9;
            color: #111;
            text-decoration: none;
            transition: background-color 0.2s, transform 0.2s;
        }

        .btn-back-icon:hover {
            background-color: #e2e2e2;
            color: #111;
            transform: scale(1.05);
        }

        /* Navigation Title */
        .board-tab-title {
            display: flex;
            justify-content: center;
            margin-top: 35px;
            margin-bottom: 25px;
            border-bottom: 1px solid #efefef;
        }
        .board-tab-title span {
            font-weight: 600;
            font-size: 16px;
            color: #111;
            padding-bottom: 8px;
            border-bottom: 3px solid #111;
        }

        /* Pinterest Masonry Grid Layout */
        .pin-grid {
            column-count: 5;
            column-gap: 16px;
        }

        @media (max-width: 1200px) { .pin-grid { column-count: 4; } }
        @media (max-width: 992px)  { .pin-grid { column-count: 3; } }
        @media (max-width: 768px)  { .pin-grid { column-count: 2; } }

        .pin-item {
            break-inside: avoid;
            margin-bottom: 16px;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .pin-card-masonry {
            border-radius: 16px;
            overflow: hidden;
            background-color: #e9e9e9;
            position: relative;
            transition: opacity 0.2s ease;
        }

        .pin-card-masonry:hover {
            opacity: 0.9;
        }

        .pin-card-masonry img {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 16px;
        }

        .pin-title-text {
            font-size: 14px;
            font-weight: 600;
            margin-top: 6px;
            padding-left: 4px;
            color: #111;
            word-wrap: break-word;
        }

        @media (max-width: 576px) {
            .profile-avatar {
                width: 90px;
                height: 90px;
                font-size: 36px;
            }
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div class="main-content">
        <div class="container-fluid px-4 py-2">
            <!-- Header Profile Target -->
            <div class="profile-header-container">
                <!-- Info Profil (Sisi Kiri) -->
                <div class="d-flex gap-3 align-items-start">
                    <div class="profile-avatar">
                        <?php if ($profile_pic_src): ?>
                            <img src="<?= $profile_pic_src; ?>" alt="Foto Profil">
                        <?php else: ?>
                            <?= $inisial; ?>
                        <?php endif; ?>
                    </div>

                    <div class="profile-info">
                        <div class="profile-title-row">
                            <h1 class="profile-name"><?= htmlspecialchars($nama_user); ?></h1>
                        </div>

                        <div class="profile-username">@<?= htmlspecialchars($username_user); ?></div>
                        <?php if(!empty($bio_user)): ?>
                            <p class="text-muted small mb-0 mt-1"><?= htmlspecialchars($bio_user); ?></p>
                        <?php endif; ?>

                        <?php if ($id_user_login != $user_id_target): ?>
                            <div class="profile-action-row">
                                <button class="btn btn-custom-gray">Pesan</button>
                                
                                <!-- FORM DENGAN TOMBOL FOLLOW / UNFOLLOW -->
                                <form action="/galeri_foto/backend/controllers/follow_process.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="id_following" value="<?= $user_id_target; ?>">
                                    <?php if ($is_following): ?>
                                        <button type="submit" class="btn btn-custom-gray">Mengikuti</button>
                                    <?php else: ?>
                                        <button type="submit" class="btn btn-custom-red">Ikuti</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tombol Icon Kembali ke Halaman Detail Sebelumnya (Sisi Kanan) -->
                <a href="javascript:history.back()" class="btn-back-icon" title="Kembali ke Detail">
                    <i class="fa-solid fa-arrow-left fs-5"></i>
                </a>
            </div>

            <!-- Tab Title -->
            <div class="board-tab-title">
                <span>Created</span>
            </div>

            <!-- Pinterest Masonry Grid -->
            <div class="pin-grid">
                <?php
                // Query mengambil semua foto yang diunggah oleh user target
                $qFoto = "SELECT * FROM foto WHERE id_user = ? ORDER BY tanggal_ungahan DESC";
                $stmtF = $koneksi->prepare($qFoto);
                $stmtF->bind_param("i", $user_id_target);
                $stmtF->execute();
                $resFoto = $stmtF->get_result();

                if ($resFoto->num_rows > 0):
                    while ($foto = $resFoto->fetch_assoc()):
                        // Tentukan path gambar pin/foto
                        $pin_img_src = $upload_path . htmlspecialchars($foto['lokasi_file']);
                        if (!file_exists($sys_upload_dir . $foto['lokasi_file']) && file_exists(__DIR__ . '/../../uploads/' . $foto['lokasi_file'])) {
                            $pin_img_src = '../../uploads/' . htmlspecialchars($foto['lokasi_file']);
                        }
                ?>
                    <a href="detail.php?id=<?= $foto['id_foto']; ?>" class="pin-item">
                        <div class="pin-card-masonry">
                            <img src="<?= $pin_img_src; ?>" alt="<?= htmlspecialchars($foto['judul_foto']); ?>">
                        </div>
                        <?php if (!empty($foto['judul_foto'])): ?>
                            <div class="pin-title-text"><?= htmlspecialchars($foto['judul_foto']); ?></div>
                        <?php endif; ?>
                    </a>
                <?php 
                    endwhile; 
                else: 
                ?>
                    <div class="text-center py-5 text-muted w-100" style="column-span: all;">
                        <p>Pengguna ini belum mengunggah foto apapun.</p>
                    </div>
                <?php 
                endif; 
                $stmtF->close(); 
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/bootstrap.bundle.min.js"></script>
</body>
</html>