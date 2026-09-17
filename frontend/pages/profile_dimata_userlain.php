<?php
// Koneksi ke database (Sesuaikan dengan file koneksi kamu)
// include 'config/koneksi.php';

// Contoh logika menerima ID user lain yang diklik dari parameter URL: profile_dimata_userlain.php?user_id=4
$user_id_target = isset($_GET['user_id']) ? $_GET['user_id'] : '';

/* 
  Contoh Query Database (Bisa disesuaikan nanti):
  $queryUser = mysqli_query($koneksi, "SELECT * FROM users WHERE id = '$user_id_target'");
  $userData = mysqli_fetch_assoc($queryUser);
*/

// Data dummy untuk simulasi tampilan
$nama_user     = "Johanna González";
$username_user = "jgonzlez0080";
$followers     = "1k";
$following     = "5";
$inisial       = strtoupper(substr($nama_user, 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nama_user; ?> (@<?php echo $username_user; ?>) - Profil</title>
    
    <!-- Bootstrap 5 CSS & FontAwesome Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            background-color: #ffffff;
            color: #111111;
        }

        /* Profile Header Styling */
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: #72cb96;
            color: #111;
            font-size: 50px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-name {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .profile-username {
            color: #5f5f5f;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .profile-stats {
            font-size: 14px;
            font-weight: 600;
            color: #111;
        }

        /* Action Buttons */
        .btn-custom-gray {
            background-color: #e9e9e9;
            color: #111;
            font-weight: 600;
            border-radius: 24px;
            padding: 10px 18px;
            border: none;
        }
        .btn-custom-gray:hover {
            background-color: #e2e2e2;
        }

        .btn-custom-red {
            background-color: #e60023;
            color: #ffffff;
            font-weight: 600;
            border-radius: 24px;
            padding: 10px 18px;
            border: none;
        }
        .btn-custom-red:hover {
            background-color: #ad081b;
            color: #ffffff;
        }

        .icon-btn {
            background: transparent;
            border: none;
            font-size: 20px;
            color: #111;
            padding: 8px 12px;
            border-radius: 50%;
        }
        .icon-btn:hover {
            background-color: #f0f0f0;
        }

        /* Custom Tabs (Collages & Board) */
        .nav-tabs-custom {
            border-bottom: none;
            justify-content: center;
            gap: 16px;
            margin-top: 30px;
            margin-bottom: 25px;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            color: #111;
            font-weight: 600;
            font-size: 16px;
            padding: 8px 4px;
            background: transparent;
            position: relative;
        }

        .nav-tabs-custom .nav-link.active {
            color: #111;
            background: transparent;
        }

        .nav-tabs-custom .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #111;
            border-radius: 2px;
        }

        /* Board Grid Styling */
        .board-card {
            background-color: #e9e9e9;
            border-radius: 16px;
            overflow: hidden;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .board-card:hover {
            opacity: 0.9;
        }

        /* Grid Cover 3 Foto dalam Board Card */
        .board-cover-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            grid-gap: 2px;
            height: 160px;
            background-color: #e9e9e9;
        }
        .board-cover-grid .main-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .board-cover-grid .side-imgs {
            display: grid;
            grid-template-rows: 1fr 1fr;
            grid-gap: 2px;
            height: 100%;
        }
        .board-cover-grid .side-imgs img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .board-title {
            font-size: 18px;
            font-weight: 700;
            margin-top: 8px;
            margin-bottom: 2px;
        }
        .board-meta {
            font-size: 12px;
            color: #5f5f5f;
        }
    </style>
</head>
<body>

    <div class="container py-4">

        <!-- Header Profil User Lain -->
        <div class="row align-items-center mb-4">
            <!-- Foto / Avatar User -->
            <div class="col-auto">
                <div class="profile-avatar">
                    <?php echo $inisial; ?>
                </div>
            </div>

            <!-- Detail Info User & Tombol Aksi -->
            <div class="col">
                <h1 class="profile-name"><?php echo $nama_user; ?></h1>
                <div class="profile-username"><?php echo $username_user; ?></div>
                <div class="profile-stats mb-3"><?php echo $followers; ?> followers · <?php echo $following; ?> following</div>

                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-custom-gray">Message</button>
                    <button class="btn btn-custom-red">Follow</button>
                </div>
            </div>

            <!-- Icon Opsi -->
            <div class="col-auto align-self-start">
                <button class="icon-btn" title="Share"><i class="fa-solid fa-upload"></i></button>
                <button class="icon-btn" title="More options"><i class="fa-solid fa-ellipsis"></i></button>
            </div>
        </div>

        <!-- Tab Navigasi: Collages & Board -->
        <ul class="nav nav-tabs nav-tabs-custom" id="profileTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="collages-tab" data-bs-toggle="tab" data-bs-target="#collages-tab-pane" type="button" role="tab">Collages</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="board-tab" data-bs-toggle="tab" data-bs-target="#board-tab-pane" type="button" role="tab">Board</button>
            </li>
        </ul>

        <!-- Konten Tab -->
        <div class="tab-content" id="profileTabContent">

            <!-- Tab Collages -->
            <div class="tab-pane fade" id="collages-tab-pane" role="tabpanel">
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-layer-group fa-3x mb-3"></i>
                    <p>Pengguna ini belum membuat collage.</p>
                </div>
            </div>

            <!-- Tab Board -->
            <div class="tab-pane fade show active" id="board-tab-pane" role="tabpanel">
                <div class="row g-3">

                    <!-- Card Board 1 -->
                    <div class="col-6 col-md-4 col-lg-2-4">
                        <div class="board-card mb-2">
                            <div class="board-cover-grid">
                                <img src="https://picsum.photos/300/400?random=1" class="main-img" alt="Cover">
                                <div class="side-imgs">
                                    <img src="https://picsum.photos/200/200?random=2" alt="Thumb 1">
                                    <img src="https://picsum.photos/200/200?random=3" alt="Thumb 2">
                                </div>
                            </div>
                        </div>
                        <div class="board-title">peinados</div>
                        <div class="board-meta">462 Pins · 3 mo</div>
                    </div>

                    <!-- Card Board 2 -->
                    <div class="col-6 col-md-4 col-lg-2-4">
                        <div class="board-card mb-2">
                            <div class="board-cover-grid">
                                <img src="https://picsum.photos/300/400?random=4" class="main-img" alt="Cover">
                                <div class="side-imgs">
                                    <img src="https://picsum.photos/200/200?random=5" alt="Thumb 1">
                                    <img src="https://picsum.photos/200/200?random=6" alt="Thumb 2">
                                </div>
                            </div>
                        </div>
                        <div class="board-title">videos peinados</div>
                        <div class="board-meta">83 Pins · 3 mo</div>
                    </div>

                    <!-- Card Board 3 -->
                    <div class="col-6 col-md-4 col-lg-2-4">
                        <div class="board-card mb-2">
                            <div class="board-cover-grid">
                                <img src="https://picsum.photos/300/400?random=7" class="main-img" alt="Cover">
                                <div class="side-imgs">
                                    <img src="https://picsum.photos/200/200?random=8" alt="Thumb 1">
                                    <img src="https://picsum.photos/200/200?random=9" alt="Thumb 2">
                                </div>
                            </div>
                        </div>
                        <div class="board-title">decoración cuarto para bebé</div>
                        <div class="board-meta">6 Pins · 7 mo</div>
                    </div>

                    <!-- Card Board 4 -->
                    <div class="col-6 col-md-4 col-lg-2-4">
                        <div class="board-card mb-2">
                            <div class="board-cover-grid">
                                <img src="https://picsum.photos/300/400?random=10" class="main-img" alt="Cover">
                                <div class="side-imgs">
                                    <img src="https://picsum.photos/200/200?random=11" alt="Thumb 1">
                                    <img src="https://picsum.photos/200/200?random=12" alt="Thumb 2">
                                </div>
                            </div>
                        </div>
                        <div class="board-title">decoración Timoteo</div>
                        <div class="board-meta">500 Pins · 7 mo</div>
                    </div>

                    <!-- Card Board 5 -->
                    <div class="col-6 col-md-4 col-lg-2-4">
                        <div class="board-card mb-2">
                            <div class="board-cover-grid">
                                <img src="https://picsum.photos/300/400?random=13" class="main-img" alt="Cover">
                                <div class="side-imgs">
                                    <img src="https://picsum.photos/200/200?random=14" alt="Thumb 1">
                                    <img src="https://picsum.photos/200/200?random=15" alt="Thumb 2">
                                </div>
                            </div>
                        </div>
                        <div class="board-title">decoración uñas</div>
                        <div class="board-meta">510 Pins · 10 mo</div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/bootstrap.bundle.min.js"></script>
</body>
</html>