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

// Ambil data profile TERBARU dari database
$qUser = $koneksi->prepare("SELECT username, email, foto_profil FROM user WHERE id_user = ?");
$qUser->bind_param("i", $id_user_login);
$qUser->execute();
$resUser = $qUser->get_result();
if ($dataUser = $resUser->fetch_assoc()) {
    $_SESSION['username'] = $dataUser['username'];
    $_SESSION['email'] = $dataUser['email'];
    $_SESSION['foto_profil'] = $dataUser['foto_profil'];
}
$qUser->close();

// Ambil tab dari URL, default ke 'boards'
$tab = $_GET['tab'] ?? 'boards';

// Include Partial Navbar & Header
include '../partials/header.php';
include '../partials/navbar.php';
?>

<main class="main-content">
    <div class="profile-header-container">

        <!-- Header Profil Presisi Sesuai Gambar -->
        <div class="profile-top-row">

            <!-- Sisi Kiri: Judul & Tab Navigasi -->
            <div class="profile-left-col">
                <h1 class="saved-ideas-title">Your saved ideas</h1>

                <div class="profile-tabs">
                    <a href="?tab=liked" data-tab="liked" class="tab-item <?= $tab === 'liked' ? 'active' : '' ?>">Liked</a>
                    <a href="?tab=boards" data-tab="boards" class="tab-item <?= $tab === 'boards' ? 'active' : '' ?>">Boards</a>
                    <a href="?tab=collages" data-tab="collages" class="tab-item <?= $tab === 'collages' ? 'active' : '' ?>">Collages</a>
                </div>
            </div>

            <!-- Sisi Kanan: Avatar, Nama, Following, Share Profile -->
            <div class="profile-right-col">
                
                <!-- Wrapper Avatar Profil -->
                <div class="profile-avatar-wrapper">
                    <a href="profile_pribadi.php" class="avatar-link" title="Lihat Profil">
                        <?php if (!empty($_SESSION['foto_profil'])): ?>
                            <img src="../../uploads/<?= htmlspecialchars($_SESSION['foto_profil']); ?>?v=<?= time(); ?>" alt="Profile Picture" class="avatar-img">
                        <?php else: ?>
                            <div class="avatar-placeholder">
                                <?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </a>

                    <!-- Tombol Pensil Memicu Modal Pop-Up -->
                    <button type="button" class="edit-avatar-btn" id="openModalBtn" title="Change picture">
                        <i class="fa-solid fa-pencil"></i>
                    </button>
                </div>

                <!-- Info Username & Following -->
                <div class="profile-details">
                    <h2>
                        <a href="profile_pribadi.php" class="username-link">
                            <?= htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                        </a>
                    </h2>
                    <p class="following-count">0 following</p>
                </div>

                <!-- Tombol Share Profile -->
                <button type="button" class="btn-share-profile">Share profile</button>
            </div>

        </div>

        <!-- Area Dynamic Tab Content -->
        <div id="tab-content">
            <?php include 'get_tab_content.php'; ?>
        </div>

    </div>
</main>

<!-- Modal Pop-up Change Your Picture -->
<div id="avatarModal" class="modal-overlay">
    <div class="modal-content">
        <span class="modal-close" id="closeModalBtn">&times;</span>
        <h2>Change your picture</h2>
        
        <!-- Form mengirimkan gambar ke file ganti_profile_process.php -->
        <form action="../../backend/controllers/ganti_profile_process.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="foto_profil" id="fileInput" accept="image/*" style="display: none;" required onchange="this.form.submit()">
            <button type="button" class="btn-choose-photo" onclick="document.getElementById('fileInput').click()">Choose photo</button>
        </form>
    </div>
</div>

<style>
/* CSS Layout Utama Sesuai Gambar */
.profile-header-container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding-top: 10px;
}

.profile-top-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 24px;
}

.saved-ideas-title {
    font-size: 32px;
    font-weight: 700;
    color: #111;
    margin: 0 0 20px 0;
}

.profile-tabs {
    display: flex;
    gap: 16px;
}

.tab-item {
    text-decoration: none;
    color: #111;
    font-size: 16px;
    font-weight: 600;
    padding: 8px 16px;
    border-radius: 20px;
    transition: background 0.2s;
}

.tab-item.active {
    background-color: #111;
    color: #fff;
}

/* Sisi Kanan (Avatar & Info User) */
.profile-right-col {
    display: flex;
    align-items: center;
    gap: 16px;
}

/* Wrapper Avatar & Tombol Pensil */
.profile-avatar-wrapper {
    position: relative;
    width: 60px;
    height: 60px;
    flex-shrink: 0;
}

.avatar-link {
    display: flex;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    text-decoration: none;
    overflow: hidden;
}

.avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    background-color: #7bdcb5;
    color: #111;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: 600;
    border-radius: 50%;
}

/* Tombol Pensil Bulat Kecil */
.edit-avatar-btn {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 24px;
    height: 24px;
    background-color: #ffffff;
    border: 1px solid #d0d0d0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 0;
    font-size: 11px;
    color: #111;
    z-index: 2;
}

.edit-avatar-btn:hover {
    background-color: #f0f0f0;
}

/* Info Username & Following */
.profile-details h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    line-height: 1.2;
}

.username-link {
    text-decoration: none;
    color: #111;
}

.username-link:hover {
    text-decoration: underline;
}

.following-count {
    margin: 2px 0 0 0;
    font-size: 13px;
    color: #5f5f5f;
}

/* Tombol Share Profile */
.btn-share-profile {
    background-color: #e9e9e9;
    color: #111;
    border: none;
    padding: 10px 18px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
    margin-left: 8px;
}

.btn-share-profile:hover {
    background-color: #e2e2e2;
}

/* Modal Pop-up CSS */
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
</style>

<script>
// Event Modal Edit Profile
const modal = document.getElementById('avatarModal');
const openBtn = document.getElementById('openModalBtn');
const closeBtn = document.getElementById('closeModalBtn');

if (openBtn) openBtn.onclick = () => modal.style.display = 'flex';
if (closeBtn) closeBtn.onclick = () => modal.style.display = 'none';

window.onclick = (e) => {
    if (e.target === modal) modal.style.display = 'none';
};

// Script Tab AJAX
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
            .catch(err => console.error('Gagal load tab:', err));
    });
});
</script>

</body>
</html>