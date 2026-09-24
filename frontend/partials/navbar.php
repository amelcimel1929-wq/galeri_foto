<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Koneksi ke database jika variabel $koneksi belum ada
if (!isset($koneksi)) {
    include_once __DIR__ . '/../../backend/config/connection.php';
}

// Deteksi halaman aktif untuk indikator navbar
$current_page = basename($_SERVER['PHP_SELF']);

// Variabel default
$foto_profil_nav = '';
$username_nav = $_SESSION['username'] ?? 'User';
$email_nav = $_SESSION['email'] ?? '';

// Ambil data foto_profil, username, & email TERBARU langsung dari Database
if (isset($_SESSION['id_user']) && isset($koneksi)) {
    $id_user_nav = $_SESSION['id_user'];
    $qUserNav = $koneksi->prepare("SELECT username, email, foto_profil FROM user WHERE id_user = ?");
    $qUserNav->bind_param("i", $id_user_nav);
    $qUserNav->execute();
    $resUserNav = $qUserNav->get_result();
    if ($dataNav = $resUserNav->fetch_assoc()) {
        $username_nav = $dataNav['username'];
        $email_nav = $dataNav['email'];
        $foto_profil_nav = $dataNav['foto_profil'];
        
        $_SESSION['email'] = $email_nav;
        $_SESSION['username'] = $username_nav;
        $_SESSION['foto_profil'] = $foto_profil_nav;
    }
    $qUserNav->close();
}

// Cek foto profil di folder backend/uploads atau uploads
$avatar_src_nav = null;
if (!empty($foto_profil_nav)) {
    if (file_exists(__DIR__ . '/../../backend/uploads/' . $foto_profil_nav)) {
        $avatar_src_nav = '/galeri_foto/backend/uploads/' . htmlspecialchars($foto_profil_nav);
    } elseif (file_exists(__DIR__ . '/../../uploads/' . $foto_profil_nav)) {
        $avatar_src_nav = '/galeri_foto/uploads/' . htmlspecialchars($foto_profil_nav);
    }
}
?>

<input type="hidden" id="userId" value="<?php echo $_SESSION['id_user'] ?? ''; ?>">

<!-- FontAwesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 0;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif;
    background-color: #ffffff;
}

/* SIDEBAR KIRI */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 72px;
    height: 100vh;
    background-color: #ffffff;
    border-right: 1px solid #e0e0e0;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0 24px 0;
    z-index: 1000;
}

.sidebar-menu {
    display: flex;
    flex-direction: column;
    gap: 24px;
    align-items: center;
}

.sidebar-bottom {
    margin-top: auto;
    position: relative; /* Acuan popover kecil */
}

/* Ikon Sidebar */
.sidebar-menu a, .sidebar-bottom a {
    color: #111;
    font-size: 24px;
    width: 52px;
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    text-decoration: none;
    transition: background 0.2s, color 0.2s;
    cursor: pointer;
}

.sidebar-menu a.sidebar-logo {
    padding: 4px;
    margin-top: 2px;
}

.sidebar-menu a.sidebar-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.sidebar-menu a:hover, .sidebar-bottom a:hover {
    background-color: #f0f0f0;
}

.sidebar-menu a.active, .sidebar-bottom a.active {
    background-color: #111111 !important;
    color: #ffffff !important;
}

/* 1. KOTAK KECIL POPOVER (HANYA MUNCUL DI ATAS GEAR SETTING) */
.setting-popover-card {
    display: none;
    position: absolute;
    bottom: 0px;
    left: 64px;
    background-color: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 8px 12px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    z-index: 9999;
    white-space: nowrap;
}

.setting-popover-card.show {
    display: block;
}

.setting-popover-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #111;
    text-decoration: none;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    padding: 6px 10px;
    border-radius: 8px;
    transition: background 0.2s;
}

.setting-popover-item:hover {
    background-color: #f0f0f0;
}

/* 2. MODAL FORM UBAH PASSWORD (Tampilan Kotak Putih Besar Melayang di Atas Halaman) */
.modal-password-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.4);
    z-index: 10000;
    align-items: center;
    justify-content: center;
}

.modal-password-overlay.show {
    display: flex;
}

.modal-password-card {
    background: #ffffff;
    width: 100%;
    max-width: 420px;
    padding: 32px;
    border-radius: 24px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    position: relative;
}

.modal-password-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.modal-password-title {
    font-size: 22px;
    font-weight: 700;
    color: #111;
    margin: 0;
}

.modal-close-btn {
    background: transparent;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: #5f5f5f;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close-btn:hover {
    background-color: #f0f0f0;
}

.modal-form-group {
    margin-bottom: 18px;
}

.modal-form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

/* Input + Ikon Mata */
.password-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.password-input-wrapper input {
    width: 100%;
    padding: 12px 40px 12px 14px;
    border: 1px solid #ccc;
    border-radius: 12px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s;
}

.password-input-wrapper input:focus {
    border-color: #111;
}

.toggle-password-btn {
    position: absolute;
    right: 14px;
    cursor: pointer;
    color: #767676;
    font-size: 16px;
    transition: color 0.2s;
}

.toggle-password-btn:hover {
    color: #111;
}

.btn-submit-password {
    width: 100%;
    background-color: #e60023;
    color: #fff;
    padding: 14px;
    border: none;
    border-radius: 24px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 10px;
    transition: background 0.2s;
}

.btn-submit-password:hover {
    background-color: #ad081b;
}

/* TOP NAVBAR */
.top-navbar {
    position: fixed;
    top: 0;
    left: 72px;
    right: 0;
    height: 56px;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 24px;
    gap: 16px;
    z-index: 999;
    transition: left 0.3s ease;
}

.top-navbar.shifted {
    left: 432px;
}

.search-box {
    background-color: #e9e9e9;
    border-radius: 24px;
    padding: 8px 16px;
    display: flex;
    align-items: center;
    flex: 1;
    max-width: 100%;
}

.search-box .search-icon {
    color: #767676;
    font-size: 15px;
}

.search-box input {
    border: none;
    background: transparent;
    outline: none;
    margin-left: 10px;
    width: 100%;
    font-size: 14px;
    color: #111;
}

.search-box .mic-icon {
    color: #111;
    font-size: 16px;
    cursor: pointer;
}

.top-nav-right {
    display: flex;
    align-items: center;
    gap: 6px;
    position: relative;
}

.profile-avatar-small {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: #7bdcb5;
    color: #111;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    border: 1px solid #333;
    overflow: hidden;
}

.nav-avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.btn-dropdown-trigger {
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 4px;
    border-radius: 50%;
    color: #5f5f5f;
    font-size: 12px;
}

.btn-dropdown-trigger:hover {
    background-color: #f0f0f0;
}

/* USER DROPDOWN */
.user-dropdown-menu {
    display: none;
    position: absolute;
    top: 45px;
    right: 0;
    width: 280px;
    background-color: #ffffff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    padding: 16px;
    z-index: 9999;
}

.user-dropdown-menu.show {
    display: block;
}

.dropdown-header-label {
    font-size: 11px;
    color: #5f5f5f;
    margin-bottom: 8px;
}

.user-profile-info {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px;
    border-radius: 12px;
    text-decoration: none;
    color: inherit;
    margin-bottom: 8px;
}

.user-profile-info:hover {
    background-color: #f0f0f0;
}

.avatar-large {
    width: 48px;
    height: 48px;
    font-size: 20px;
    background-color: #e60023;
    color: #fff;
    border: none;
    flex-shrink: 0;
}

.user-details {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-weight: 700;
    font-size: 15px;
    color: #111;
}

.user-type, .user-email {
    font-size: 12px;
    color: #767676;
    word-break: break-all;
}

.dropdown-section {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-bottom: 8px;
}

.dropdown-item {
    padding: 8px;
    border-radius: 8px;
    text-decoration: none;
    color: #111;
    font-size: 14px;
}

.dropdown-item:hover {
    background-color: #f0f0f0;
}

.bold-text {
    font-weight: 600;
}

.text-danger {
    color: #e60023;
}

/* MAIN CONTENT SHIFT */
.main-content {
    margin-left: 72px;
    margin-top: 56px;
    padding-top: 10px;
    padding-left: 24px;
    padding-right: 24px;
    transition: margin-left 0.3s ease;
}

.main-content.shifted {
    margin-left: 432px;
}

/* CREATE PANEL & NOTIFIKASI PANEL */
.create-panel, .notification-panel {
    position: fixed;
    left: 72px;
    top: 0;
    width: 360px;
    height: 100vh;
    background-color: #ffffff;
    border-right: 1px solid #e0e0e0;
    padding: 24px 16px;
    display: none;
    flex-direction: column;
    z-index: 998;
    box-shadow: 4px 0 15px rgba(0, 0, 0, 0.05);
    overflow-y: auto;
}

.create-panel.open, .notification-panel.open { 
    display: flex !important; 
}

.panel-header, .notif-header { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    margin-bottom: 16px; 
}

.panel-title, .notif-title { 
    font-size: 20px; 
    font-weight: 700; 
    color: #111; 
}

.close-btn { 
    border: none; 
    background: transparent; 
    font-size: 18px; 
    cursor: pointer; 
    width: 32px; 
    height: 32px; 
    border-radius: 50%; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
}

.close-btn:hover { 
    background-color: #e9e9e9; 
}

.option-list { display: flex; flex-direction: column; gap: 12px; }
.option-item { display: flex; align-items: flex-start; padding: 12px; border-radius: 16px; background-color: #efefef; cursor: pointer; text-decoration: none; color: inherit; transition: background-color 0.2s; }
.option-item:hover { background-color: #e2e2e2; }
.option-icon { width: 40px; height: 40px; font-size: 18px; display: flex; align-items: center; justify-content: center; margin-right: 12px; flex-shrink: 0; }
.option-text h4 { font-size: 15px; font-weight: 600; color: #111; margin-bottom: 2px; }
.option-text p { font-size: 12px; color: #5f5f5f; line-height: 1.3; }
</style>

<!-- Sidebar Kiri -->
<aside class="sidebar">
    <nav class="sidebar-menu">
        <a href="/galeri_foto/index.php" class="sidebar-logo" title="Galeri Foto">
            <img src="/galeri_foto/backend/uploads/logo.png" alt="Logo">
        </a>
        <a href="/galeri_foto/index.php" class="<?php echo ($current_page === 'index.php') ? 'active' : ''; ?>" title="Beranda"><i class="fa-solid fa-house"></i></a>
        <a href="/galeri_foto/frontend/pages/profile.php" class="<?php echo ($current_page === 'profile.php') ? 'active' : ''; ?>" title="Kategori"><i class="fa-solid fa-table-cells"></i></a>
        <a href="javascript:void(0)" id="btnTambah" title="Buat"><i class="fa-regular fa-square-plus"></i></a>
        <a href="javascript:void(0)" id="btnNotifikasi" title="Notifikasi"><i class="fa-regular fa-bell"></i></a>
        <a href="#" class="<?php echo ($current_page === 'pesan.php') ? 'active' : ''; ?>" title="Pesan"><i class="fa-regular fa-comment-dots"></i></a>
    </nav>
    <div class="sidebar-bottom">
        <a href="javascript:void(0)" id="btnSetting" title="Pengaturan"><i class="fa-solid fa-gear"></i></a>
        
        <!-- POPUP KECIL PENGATURAN -->
        <div class="setting-popover-card" id="settingPopoverCard">
            <div class="setting-popover-item" id="btnOpenPasswordModal">
                <i class="fa-solid fa-key"></i>
                <span>ubah password?</span>
            </div>
        </div>
    </div>
</aside>

<!-- MODAL FORM UBAH PASSWORD MELAYANG -->
<div class="modal-password-overlay" id="modalPasswordOverlay">
    <div class="modal-password-card">
        <div class="modal-password-header">
            <h3 class="modal-password-title">Ubah Kata Sandi</h3>
            <button type="button" class="modal-close-btn" id="btnClosePasswordModal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form action="/galeri_foto/backend/controllers/process_ganti_password.php" method="POST">
            
            <div class="modal-form-group">
                <label>Kata Sandi Saat Ini</label>
                <div class="password-input-wrapper">
                    <input type="password" name="current_password" required>
                    <i class="fa-regular fa-eye toggle-password-btn"></i>
                </div>
            </div>

            <div class="modal-form-group">
                <label>Kata Sandi Baru</label>
                <div class="password-input-wrapper">
                    <input type="password" name="new_password" minlength="6" required>
                    <i class="fa-regular fa-eye toggle-password-btn"></i>
                </div>
            </div>

            <div class="modal-form-group">
                <label>Konfirmasi Kata Sandi Baru</label>
                <div class="password-input-wrapper">
                    <input type="password" name="confirm_password" minlength="6" required>
                    <i class="fa-regular fa-eye toggle-password-btn"></i>
                </div>
            </div>

            <button type="submit" class="btn-submit-password">Simpan Password Baru</button>
        </form>
    </div>
</div>



<!-- Top Navbar -->
<header class="top-navbar" id="topNavbar">
    <div class="search-box">
        <i class="fa-solid fa-magnifying-glass search-icon"></i>
        <form id="searchForm" action="/galeri_foto/index.php" method="GET" style="width: 100%;">
            <input type="text" id="searchInput" name="search" placeholder="Search your Pins" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" autocomplete="off">
        </form>
        <i class="fa-solid fa-microphone mic-icon"></i>
    </div>
    
    <div class="top-nav-right">
        <a href="/galeri_foto/frontend/pages/profile.php" class="profile-avatar-small" title="Profil Saya">
            <?php if ($avatar_src_nav): ?>
                <img src="<?php echo $avatar_src_nav; ?>?v=<?php echo time(); ?>" alt="Profile" class="nav-avatar-img">
            <?php else: ?>
                <?php echo strtoupper(substr($username_nav, 0, 1)); ?>
            <?php endif; ?>
        </a>

        <button type="button" id="btnUserMenu" class="btn-dropdown-trigger">
            <i class="fa-solid fa-chevron-down"></i>
        </button>

        <div class="user-dropdown-menu" id="userDropdown">
            <div class="dropdown-header-label">Currently in</div>
            
            <a href="/galeri_foto/frontend/pages/profile.php" class="user-profile-info">
                <div class="profile-avatar-small avatar-large">
                    <?php if ($avatar_src_nav): ?>
                        <img src="<?php echo $avatar_src_nav; ?>?v=<?php echo time(); ?>" alt="Profile" class="nav-avatar-img">
                    <?php else: ?>
                        <?php echo strtoupper(substr($username_nav, 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <div class="user-details">
                    <span class="user-name"><?php echo htmlspecialchars($username_nav); ?></span>
                    <span class="user-type">Personal</span>
                    <span class="user-email"><?php echo htmlspecialchars($email_nav); ?></span>
                </div>
            </a>

            <div class="dropdown-section">
                <a href="#" class="dropdown-item bold-text">Convert to business</a>
            </div>

            <div class="dropdown-section">
                <a href="/galeri_foto/backend/controllers/auth_process.php?action=logout" class="dropdown-item bold-text text-danger">Log out</a>
            </div>
        </div>
    </div>
</header>

<!-- POP-UP CREATE PANEL -->
<div class="create-panel" id="createPanel">
    <div class="panel-header">
        <h2 class="panel-title">Create</h2>
        <button type="button" class="close-btn" id="closeBtn">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="option-list">
        <a href="/galeri_foto/frontend/pages/tambah_foto.php" class="option-item">
            <div class="option-icon"><i class="fa-solid fa-table-cells-large"></i></div>
            <div class="option-text">
                <h4>Board</h4>
                <p>Post your photos or videos and add links, stickers, effects and more</p>
            </div>
        </a>

        <a href="#" class="option-item">
            <div class="option-icon"><i class="fa-solid fa-scissors"></i></div>
            <div class="option-text">
                <h4>Collage</h4>
                <p>Mix and match ideas to build your vision and create something new</p>
            </div>
        </a>
    </div>
</div>

<!-- INCLUDE PANEL NOTIFIKASI -->
<?php include_once __DIR__ . '/../pages/notifikasi.php'; ?>

<!-- Include search.js -->
<script src="/galeri_foto/frontend/partials/search.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnTambah = document.getElementById('btnTambah');
    const closeBtn = document.getElementById('closeBtn');
    const createPanel = document.getElementById('createPanel');

    const btnNotifikasi = document.getElementById('btnNotifikasi');
    const notificationPanel = document.getElementById('notificationPanel');
    const closeNotifBtn = document.getElementById('closeNotifBtn');

    const topNavbar = document.getElementById('topNavbar');
    const mainContent = document.querySelector('.main-content');

    // LOGIKA POPOVER SETTING & MODAL PASSWORD
    const btnSetting = document.getElementById('btnSetting');
    const settingPopoverCard = document.getElementById('settingPopoverCard');
    const btnOpenPasswordModal = document.getElementById('btnOpenPasswordModal');
    const modalPasswordOverlay = document.getElementById('modalPasswordOverlay');
    const btnClosePasswordModal = document.getElementById('btnClosePasswordModal');

    // Klik Gear -> munculkan kotak kecil
    if (btnSetting && settingPopoverCard) {
        btnSetting.addEventListener('click', function(e) {
            e.stopPropagation();
            settingPopoverCard.classList.toggle('show');
        });
    }

    // Klik ubah password di kotak kecil -> buka modal melayang
    if (btnOpenPasswordModal && modalPasswordOverlay) {
        btnOpenPasswordModal.addEventListener('click', function(e) {
            e.stopPropagation();
            settingPopoverCard.classList.remove('show');
            modalPasswordOverlay.classList.add('show');
        });
    }

    // Klik X -> tutup modal
    if (btnClosePasswordModal && modalPasswordOverlay) {
        btnClosePasswordModal.addEventListener('click', function() {
            modalPasswordOverlay.classList.remove('show');
        });
    }

    // Toggle Show/Hide Password (Ikon Mata)
    document.querySelectorAll('.toggle-password-btn').forEach(function(icon) {
        icon.addEventListener('click', function() {
            const input = this.previousElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            }
        });
    });

    // Toggle Shift Function
    function toggleContentShift(shift) {
        if (shift) {
            if (topNavbar) topNavbar.classList.add('shifted');
            if (mainContent) mainContent.classList.add('shifted');
        } else {
            if (topNavbar) topNavbar.classList.remove('shifted');
            if (mainContent) mainContent.classList.remove('shifted');
        }
    }

    // Toggle Create Panel
    if (btnTambah && createPanel) {
        btnTambah.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (notificationPanel) {
                notificationPanel.classList.remove('open');
                if (btnNotifikasi) btnNotifikasi.classList.remove('active');
            }
            const isOpen = createPanel.classList.toggle('open');
            this.classList.toggle('active', isOpen);
            toggleContentShift(isOpen);
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                createPanel.classList.remove('open');
                if (btnTambah) btnTambah.classList.remove('active');
                toggleContentShift(false);
            });
        }
    }

    // Toggle Notifikasi Panel
    if (btnNotifikasi && notificationPanel) {
        btnNotifikasi.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (createPanel) {
                createPanel.classList.remove('open');
                if (btnTambah) btnTambah.classList.remove('active');
            }
            const isOpen = notificationPanel.classList.toggle('open');
            this.classList.toggle('active', isOpen);
            toggleContentShift(isOpen);
        });

        if (closeNotifBtn) {
            closeNotifBtn.addEventListener('click', function() {
                notificationPanel.classList.remove('open');
                if (btnNotifikasi) btnNotifikasi.classList.remove('active');
                toggleContentShift(false);
            });
        }
    }

    // Close Outside Click
    document.addEventListener('click', function(e) {
        let closedAny = false;
        if (createPanel && !createPanel.contains(e.target) && !btnTambah.contains(e.target)) {
            if (createPanel.classList.contains('open')) {
                createPanel.classList.remove('open');
                btnTambah.classList.remove('active');
                closedAny = true;
            }
        }
        if (notificationPanel && !notificationPanel.contains(e.target) && !btnNotifikasi.contains(e.target)) {
            if (notificationPanel.classList.contains('open')) {
                notificationPanel.classList.remove('open');
                btnNotifikasi.classList.remove('active');
                closedAny = true;
            }
        }
        if (settingPopoverCard && !settingPopoverCard.contains(e.target) && !btnSetting.contains(e.target)) {
            settingPopoverCard.classList.remove('show');
        }
        if (modalPasswordOverlay && e.target === modalPasswordOverlay) {
            modalPasswordOverlay.classList.remove('show');
        }
        if (closedAny) {
            toggleContentShift(false);
        }
    });

    // Dropdown Profile
    const btnUserMenu = document.getElementById('btnUserMenu');
    const userDropdown = document.getElementById('userDropdown');

    if (btnUserMenu && userDropdown) {
        btnUserMenu.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });

        document.addEventListener('click', function(e) {
            if (!userDropdown.contains(e.target) && !btnUserMenu.contains(e.target)) {
                userDropdown.classList.remove('show');
            }
        });
    }
});
</script>