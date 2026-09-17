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

// 1. Fetch Data User Target (Sesuai kolom tabel user)
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

$upload_path   = "../../backend/uploads/";
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
        
        /* Layout Header Profile ala Pinterest: foto kiri, info kanan, rata kiri sejajar dengan board */
        .profile-header-container {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            justify-content: flex-start;
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
        
        /* Board Navigation Title */
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

        /* Board Card Layout */
        .board-card-container { text-decoration: none; color: inherit; display: block; }
        .board-card { background-color: #e9e9e9; border-radius: 16px; overflow: hidden; cursor: pointer; transition: transform 0.2s ease; }
        .board-card-container:hover .board-card { transform: translateY(-4px); }
        .board-cover-grid { display: grid; grid-template-columns: 2fr 1fr; grid-gap: 2px; height: 160px; background-color: #e9e9e9; }
        .board-cover-grid .main-img { width: 100%; height: 100%; object-fit: cover; }
        .board-cover-grid .side-imgs { display: grid; grid-template-rows: 1fr 1fr; grid-gap: 2px; height: 100%; }
        .board-cover-grid .side-imgs img { width: 100%; height: 100%; object-fit: cover; }
        .empty-slot { background-color: #dcdcdc; width: 100%; height: 100%; }
        .board-title { font-size: 16px; font-weight: 700; margin-top: 8px; margin-bottom: 2px; }
        .board-meta { font-size: 13px; color: #5f5f5f; }

        /* Sedikit lebih rapat di layar kecil */
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

    <!-- MENYAMBUNGKAN KE NAVBAR -->
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div class="main-content">
    <div class="container py-2">
        <!-- Header Profile Target (Sejajar dan Diturunkan) -->
        <div class="profile-header-container">
            <div class="profile-avatar">
                <?php if (!empty($foto_profil) && file_exists($upload_path . $foto_profil)): ?>
                    <img src="<?= $upload_path . htmlspecialchars($foto_profil); ?>" alt="Foto Profil">
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
                        <button class="btn btn-custom-red">Ikuti</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Section Title (Hanya Board) -->
        <div class="board-tab-title">
            <span>Board</span>
        </div>

        <!-- Konten Board -->
        <div class="text-start">
            <div class="row g-3">
                <?php
                $qAlbum = "SELECT a.*, 
                           (SELECT COUNT(*) FROM save_foto sf WHERE sf.id_album = a.id_album) as total_foto 
                           FROM album a 
                           WHERE a.id_user = ? 
                           ORDER BY a.tanggal_dibuat DESC";
                $stmtA = $koneksi->prepare($qAlbum);
                $stmtA->bind_param("i", $user_id_target);
                $stmtA->execute();
                $resAlbum = $stmtA->get_result();

                if ($resAlbum->num_rows > 0):
                    while ($album = $resAlbum->fetch_assoc()):
                        // Ambil 3 foto terbaru dari save_foto berdasarkan id_album
                        $q3Foto = "SELECT f.lokasi_file 
                                   FROM save_foto sf 
                                   JOIN foto f ON sf.id_foto = f.id_foto 
                                   WHERE sf.id_album = ? 
                                   ORDER BY sf.tanggal_simpan DESC LIMIT 3";
                        $stmt3 = $koneksi->prepare($q3Foto);
                        $stmt3->bind_param("i", $album['id_album']);
                        $stmt3->execute();
                        $res3Foto = $stmt3->get_result();
                        $fotos = [];
                        while ($f = $res3Foto->fetch_assoc()) { $fotos[] = $f['lokasi_file']; }
                        $stmt3->close();
                ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <a href="detail_board.php?id_album=<?= $album['id_album']; ?>" class="board-card-container">
                            <div class="board-card mb-2">
                                <div class="board-cover-grid">
                                    <?= isset($fotos[0]) ? '<img src="'.$upload_path.htmlspecialchars($fotos[0]).'" class="main-img">' : '<div class="empty-slot"></div>'; ?>
                                    <div class="side-imgs">
                                        <?= isset($fotos[1]) ? '<img src="'.$upload_path.htmlspecialchars($fotos[1]).'">' : '<div class="empty-slot"></div>'; ?>
                                        <?= isset($fotos[2]) ? '<img src="'.$upload_path.htmlspecialchars($fotos[2]).'">' : '<div class="empty-slot"></div>'; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="board-title"><?= htmlspecialchars($album['nama_album']); ?></div>
                            <div class="board-meta"><?= $album['total_foto']; ?> Pin</div>
                        </a>
                    </div>
                <?php endwhile; else: ?>
                    <div class="text-center py-5 text-muted w-100"><p>Belum ada board publik.</p></div>
                <?php endif; $stmtA->close(); ?>
            </div>
        </div>
    </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/bootstrap.bundle.min.js"></script>
</body>
</html>