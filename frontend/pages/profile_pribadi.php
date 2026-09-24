<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../../backend/config/connection.php';

$id_user_login = $_SESSION['id_user'] ?? null;

// Jika user belum login, alihkan ke login
if (!$id_user_login) {
    header('Location: ../auth/login.php');
    exit;
}

// Data Default Fallback
$username = $_SESSION['username'] ?? 'User';
$handle = $_SESSION['username'] ?? 'user123';
$bio = 'Add a short bio to make your profile your own';
$foto_profil = $_SESSION['foto_profil'] ?? '';

// Ambil data profile TERBARU dari database
if (isset($koneksi) || isset($conn)) {
    $db = $koneksi ?? $conn;
    $qUser = $db->prepare("SELECT username, nama_lengkap, email, bio, foto_profil FROM user WHERE id_user = ?");
    $qUser->bind_param("i", $id_user_login);
    $qUser->execute();
    $resUser = $qUser->get_result();
    
    if ($data = $resUser->fetch_assoc()) {
        $username = !empty($data['nama_lengkap']) ? $data['nama_lengkap'] : ($data['username'] ?? $username);
        $handle = !empty($data['username']) ? $data['username'] : $handle;
        $bio = !empty($data['bio']) ? $data['bio'] : $bio;
        $foto_profil = $data['foto_profil'] ?? '';
        
        $_SESSION['username'] = $data['username'];
        $_SESSION['email'] = $data['email'];
        $_SESSION['foto_profil'] = $data['foto_profil'];
    }
    $qUser->close();
}

// Ambil tab aktif dari URL, default ke 'liked'
$tab = $_GET['tab'] ?? 'liked';

// FIX: Cek foto profil di 2 kemungkinan folder (backend/uploads = lokasi baku, uploads = folder lama)
$avatar_src2 = null;
if (!empty($foto_profil)) {
    if (file_exists(__DIR__ . '/../../backend/uploads/' . $foto_profil)) {
        $avatar_src2 = '../../backend/uploads/' . htmlspecialchars($foto_profil);
    } elseif (file_exists(__DIR__ . '/../../uploads/' . $foto_profil)) {
        $avatar_src2 = '../../uploads/' . htmlspecialchars($foto_profil);
    }
}

include '../partials/header.php';
include '../partials/navbar.php';
?>

<style>
/* =====================================
   RESET & BASE STYLES
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
   CONTAINER PROFIL (Diturunkan agar tidak tertutup Navbar)
===================================== */
.pinterest-profile-main {
    width: 100%;
    min-height: calc(100vh - 80px);
    padding-top: 100px;   /* Ruang untuk navbar atas */
    padding-left: 96px; /* Sejajar setelah navbar samping */
    padding-right: 28px;
    padding-bottom: 60px;
}

/* =====================================
   HEADER PROFIL
===================================== */
.profile-header-container {
    display: flex;
    align-items: flex-start;
    gap: 24px;
    margin-bottom: 20px;
    padding-left: 0; /* Avatar jadi titik acuan kiri */
}

/* =====================================
   AVATAR PROFIL
===================================== */
.avatar-wrapper {
    position: relative;
    flex-shrink: 0;
    width: 110px;
    height: 110px;
}

.avatar-circle {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background-color: #7bd6a8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
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
    bottom: 2px;
    right: 2px;
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
    cursor: pointer;
    border: 1px solid #e2e2e2;
    z-index: 2;
}

.avatar-edit-icon:hover {
    background: #f1f1f1;
}

/* =====================================
   INFORMASI PROFIL
===================================== */
.profile-info {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    padding-top: 2px;
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
    margin-bottom: 8px;
}

.profile-stats {
    font-size: 14px;
    font-weight: 600;
    color: #111111;
    margin-bottom: 10px;
}

.profile-bio {
    font-size: 14px;
    color: #111111;
    margin-bottom: 16px;
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
   TAB NAVIGASI (Liked, Boards, Collages - Tengah)
===================================== */
.tabs-container {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    gap: 16px;
    width: 100%;
    margin-top: 24px;
    margin-bottom: 24px;
}

.tab-item {
    text-decoration: none;
    color: #111111;
    font-size: 16px;
    font-weight: 600;
    padding: 8px 20px;
    border-radius: 20px;
    transition: background 0.2s;
}

.tab-item.active {
    background-color: #111111;
    color: #ffffff;
}

/* =====================================
   AREA KONTEN TAB (disejajarkan dgn avatar/icon profil)
===================================== */
#tab-content {
    width: 100%;
    margin: 0;
    padding: 0;
}

#tab-content > * {
    margin-left: 0 !important;
    padding-left: 0 !important;
}

#tab-content .row,
#tab-content .grid,
#tab-content .masonry,
#tab-content [class*="gallery"],
#tab-content [class*="pin"] {
    justify-content: flex-start !important;
    margin-left: 0 !important;
    padding-left: 0 !important;
}

/* =====================================
   CREATE BUTTON FLOATING
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
   MODAL EDIT FOTO PROFIL
===================================== */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
    z-index: 10000;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: #ffffff;
    padding: 35px 40px;
    border-radius: 24px;
    width: 100%;
    max-width: 420px;
    text-align: center;
    position: relative;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.modal-content h2 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 24px;
    color: #111;
}

.modal-close {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 22px;
    cursor: pointer;
    color: #666;
}

.btn-choose-photo {
    width: 100%;
    background-color: #e60023;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 20px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-choose-photo:hover {
    background-color: #ad081b;
}

/* RESPONSIVE */
@media (max-width: 1200px) {
    .pinterest-profile-main {
        padding-left: 92px;
        padding-right: 24px;
    }
}

@media (max-width: 768px) {
    .pinterest-profile-main {
        padding-left: 88px;
        padding-right: 16px;
        padding-top: 80px;
    }
}

@media (max-width: 520px) {
    .pinterest-profile-main { padding-left: 78px; padding-right: 12px; }
    .profile-header-container { gap: 14px; }
    .avatar-wrapper { width: 76px; height: 76px; }
    .avatar-circle { font-size: 34px; }
    .profile-name { font-size: 21px; }
    .tabs-container { justify-content: flex-start; gap: 4px; }
}
</style>

<!-- HALAMAN PROFIL UTAMA -->
<div class="pinterest-profile-main">

    <!-- HEADER PROFIL -->
    <div class="profile-header-container">

        <!-- AVATAR PROFIL -->
        <div class="avatar-wrapper">
            <div class="avatar-circle">
                <?php if ($avatar_src2): ?>
                    <img src="<?= $avatar_src2; ?>?v=<?= time(); ?>" alt="Profile Picture">
                <?php else: ?>
                    <?= strtoupper(substr($username, 0, 1)); ?>
                <?php endif; ?>
            </div>

            <button type="button" class="avatar-edit-icon" id="openModalBtn" title="Change profile picture">
                <i class="fa-solid fa-pencil"></i>
            </button>
        </div>

        <!-- INFORMASI USER -->
        <div class="profile-info">
            <h1 class="profile-name">
                <?= htmlspecialchars($username); ?>
            </h1>

            <div class="profile-handle">
                <?= htmlspecialchars($handle); ?>
            </div>

            <div class="profile-stats">
                0 followers · 0 following
            </div>

            <div class="profile-bio">
                <span>
                    <?= htmlspecialchars($bio); ?>
                </span>
                <i class="fa-solid fa-pencil bio-pencil"></i>
            </div>

            <div class="btn-group">
                <button class="btn-pinterest">
                    <i class="fa-solid fa-arrow-up-from-bracket"></i>
                    Share profile
                </button>

                <a href="../../frontend/pages/deskripsi.php" class="btn-pinterest" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-pencil"></i>
                    Edit profile
                </a>
            </div>
        </div>

    </div>

    <!-- TAB NAVIGASI (Liked, Boards, Collages - Posisi Tengah) -->
    <div class="tabs-container">
        <a href="?tab=liked" data-tab="liked" class="tab-item <?= $tab === 'liked' ? 'active' : '' ?>">Liked</a>
        <a href="?tab=boards" data-tab="boards" class="tab-item <?= $tab === 'boards' ? 'active' : '' ?>">Boards</a>
        <a href="?tab=collages" data-tab="collages" class="tab-item <?= $tab === 'collages' ? 'active' : '' ?>">Collages</a>
    </div>

    <!-- DYNAMIC TAB CONTENT -->
    <div id="tab-content">
        <?php include 'get_tab_content.php'; ?>
    </div>

    <!-- BUTTON CREATE FLOATING -->
    <a href="tambah_foto.php" class="floating-create-btn">
        Create
    </a>

</div>

<!-- MODAL POP-UP EDIT FOTO PROFIL -->
<div id="avatarModal" class="modal-overlay">
    <div class="modal-content">
        <span class="modal-close" id="closeModalBtn">&times;</span>
        <h2>Change your picture</h2>
        
        <form action="../../backend/controllers/ganti_profile_process.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="foto_profil" id="fileInput" accept="image/*" style="display: none;" required onchange="this.form.submit()">
            <button type="button" class="btn-choose-photo" onclick="document.getElementById('fileInput').click()">Choose photo</button>
        </form>
    </div>
</div>

<script>
// Logic Modal Pop-up Foto
const modal = document.getElementById('avatarModal');
const openBtn = document.getElementById('openModalBtn');
const closeBtn = document.getElementById('closeModalBtn');

if (openBtn) openBtn.onclick = () => modal.style.display = 'flex';
if (closeBtn) closeBtn.onclick = () => modal.style.display = 'none';

window.onclick = (e) => {
    if (e.target === modal) modal.style.display = 'none';
};

// AJAX Script Pindah Tab Tanpa Reload
document.querySelectorAll('.tab-item').forEach(tab => {
    tab.addEventListener('click', function (e) {
        e.preventDefault();

        const selectedTab = this.dataset.tab;

        document.querySelectorAll('.tab-item').forEach(t => t.classList.remove('active'));
        this.classList.add('active');

        window.history.pushState({}, '', '?tab=' + selectedTab);

        fetch(`get_tab_content.php?tab=${selectedTab}&_t=${new Date().getTime()}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('tab-content').innerHTML = html;
            })
            .catch(err => console.error('Gagal memuat tab:', err));
    });
});
</script>

</body>
</html>
