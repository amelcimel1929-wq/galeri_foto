<?php 
session_start();

include '../../backend/config/connection.php';

$id_user_login = $_SESSION['id_user'] ?? null;

$username = 'amelcimel';
$handle = 'amelcimel1929';
$bio = 'Add a short bio to make your profile your own';
$foto_profil = '';

if ($id_user_login && isset($conn)) {
    $query = "SELECT * FROM user WHERE id_user = '$id_user_login'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        $username = !empty($data['nama_lengkap']) 
            ? $data['nama_lengkap'] 
            : ($data['username'] ?? $username);
        $handle = !empty($data['username']) 
            ? $data['username'] 
            : $handle;
        $bio = !empty($data['bio']) 
            ? $data['bio'] 
            : $bio;
        $foto_profil = $data['foto_profil'] ?? '';
    }
}

include '../partials/header.php';
include '../partials/navbar.php';
?>

<style>
/* =====================================
   RESET
===================================== */
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    background: #ffffff;
    color: #111111;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
}

/* =====================================
   CONTAINER PROFIL
===================================== */
.pinterest-profile-main {
    width: 100%;
    min-height: calc(100vh - 70px);
    padding-top: 60px;   /* Diturunkan sedikit lagi dari search bar */
    padding-left: 55px;  /* Tetap rapat di sebelah sidebar kiri */
    padding-right: 24px;
    padding-bottom: 60px;
}

/* =====================================
   HEADER PROFIL
===================================== */
.profile-header-container {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 0;
}

/* =====================================
   AVATAR PROFIL
===================================== */
.avatar-wrapper {
    position: relative;
    flex-shrink: 0;
}

.avatar-circle {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background-color: #7bd6a8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 44px;
    font-weight: 600;
    color: #111111;
    overflow: hidden;
}

.avatar-circle img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-edit-icon {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.18);
    color: #111111;
    font-size: 12px;
    text-decoration: none;
    cursor: pointer;
    border: 1px solid #e2e2e2;
}

.avatar-edit-icon:hover {
    background: #f1f1f1;
}

/* =====================================
   INFORMASI PROFIL (SEBELAH AVATAR)
===================================== */
.profile-info {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    padding-top: 4px;
}

.profile-name {
    font-size: 26px;
    font-weight: 700;
    color: #111111;
    margin: 0 0 2px 0;
    line-height: 1.2;
}

.profile-handle {
    font-size: 14px;
    color: #5f5f5f;
    margin-bottom: 12px;
}

.profile-stats {
    font-size: 14px;
    font-weight: 600;
    color: #111111;
    margin-bottom: 12px;
}

.profile-bio {
    font-size: 14px;
    color: #111111;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.bio-pencil {
    color: #5f5f5f;
    font-size: 13px;
    cursor: pointer;
}

/* =====================================
   TOMBOL PROFIL
===================================== */
.btn-group {
    display: flex;
    gap: 8px;
}

.btn-pinterest {
    border: none;
    background-color: #e9e9e9;
    color: #111111;
    padding: 10px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    text-decoration: none;
    white-space: nowrap;
}

.btn-pinterest:hover {
    background-color: #d8d8d8;
}

/* =====================================
   TAB CREATED / SAVED
===================================== */
.tabs-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 24px;
    width: 100%;
    margin-top: 40px;
    margin-bottom: 30px;
}

.tab-btn {
    background: none;
    border: none;
    font-size: 16px;
    font-weight: 600;
    color: #111111;
    padding: 0 0 8px 0;
    cursor: pointer;
    position: relative;
}

.tab-btn.active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background-color: #111111;
    border-radius: 2px;
}

/* =====================================
   AREA KOSONG TENGAH
===================================== */
.empty-state-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    margin-top: 20px;
}

.palette-circle-bg {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    background-color: #f4e4f8;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
}

.palette-icon {
    position: relative;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: #e9bce9;
    transform: rotate(-12deg);
}

.palette-icon::before {
    content: '';
    position: absolute;
    width: 24px;
    height: 24px;
    background: #f4e4f8;
    border-radius: 50%;
    right: 10px;
    top: 15px;
}

.palette-dot {
    position: absolute;
    width: 18px;
    height: 18px;
    border-radius: 50%;
}

.dot-1 { background: #a33a96; left: 15px; top: 22px; }
.dot-2 { background: #cf71ad; left: 20px; top: 51px; }
.dot-3 { background: #e99b35; left: 45px; top: 70px; }
.dot-4 { background: #8d1e58; left: 68px; top: 47px; }
.dot-5 { background: #f15b36; left: 72px; top: 20px; }
.dot-6 { background: #d66bd0; left: 43px; top: 33px; }

.palette-brush {
    position: absolute;
    width: 6px;
    height: 60px;
    background: #9a5b12;
    top: -20px;
    right: -5px;
    transform: rotate(45deg);
    border-radius: 2px;
}

.empty-title {
    font-size: 18px;
    font-weight: 700;
    color: #111111;
    margin: 0;
    text-align: center;
}

/* =====================================
   CREATE BUTTON (FLOATING BOTTOM RIGHT)
===================================== */
.floating-create-btn {
    position: fixed;
    bottom: 28px;
    right: 28px;
    background-color: #e60023;
    color: #ffffff;
    font-weight: 600;
    font-size: 15px;
    padding: 12px 20px;
    border-radius: 24px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    text-decoration: none;
    border: none;
    cursor: pointer;
    z-index: 1000;
}

.floating-create-btn:hover {
    background-color: #ad081b;
    color: #ffffff;
}

/* =====================================
   RESPONSIVE
===================================== */
@media (max-width: 768px) {
    .pinterest-profile-main {
        padding-left: 16px;
        padding-right: 16px;
        padding-top: 30px;
    }
}
</style>

<!-- HALAMAN PROFIL -->
<div class="pinterest-profile-main">

    <!-- HEADER PROFIL -->
    <div class="profile-header-container">

        <!-- AVATAR PROFIL -->
        <div class="avatar-wrapper">
            <div class="avatar-circle">
                <?php if (!empty($foto_profil)): ?>
                    <img src="../../backend/uploads/<?php echo htmlspecialchars($foto_profil); ?>" alt="Profile">
                <?php else: ?>
                    <?php echo strtoupper(substr($username, 0, 1)); ?>
                <?php endif; ?>
            </div>

            <a href="#" class="avatar-edit-icon" title="Edit Profile Picture">
                <i class="fa-solid fa-pencil"></i>
            </a>
        </div>

        <!-- INFORMASI USER -->
        <div class="profile-info">
            <h1 class="profile-name">
                <?php echo htmlspecialchars($username); ?>
            </h1>

            <div class="profile-handle">
                <?php echo htmlspecialchars($handle); ?>
            </div>

            <div class="profile-stats">
                0 followers · 0 following
            </div>

            <div class="profile-bio">
                <span>
                    <?php echo htmlspecialchars($bio); ?>
                </span>
                <i class="fa-solid fa-pencil bio-pencil"></i>
            </div>

            <div class="btn-group">
                <button class="btn-pinterest">
                    <i class="fa-solid fa-arrow-up-from-bracket"></i>
                    Share profile
                </button>

                <button class="btn-pinterest">
                    <i class="fa-solid fa-pencil"></i>
                    Edit profile
                </button>
            </div>
        </div>

    </div>

    <!-- TAB -->
    <div class="tabs-container">
        <button class="tab-btn active">Created</button>
        <button class="tab-btn">Saved</button>
    </div>

    <!-- AREA KOSONG -->
    <div class="empty-state-section">
        <div class="palette-circle-bg">
            <div class="palette-icon">
                <span class="palette-dot dot-1"></span>
                <span class="palette-dot dot-2"></span>
                <span class="palette-dot dot-3"></span>
                <span class="palette-dot dot-4"></span>
                <span class="palette-dot dot-5"></span>
                <span class="palette-dot dot-6"></span>
                <span class="palette-brush"></span>
            </div>
        </div>

        <h2 class="empty-title">Create your first Pin</h2>
    </div>

    <!-- BUTTON CREATE FLOATING -->
    <a href="tambah_foto.php" class="floating-create-btn">
        Create
    </a>

</div>

</body>
</html>