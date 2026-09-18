<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Koneksi ke database jika variabel $koneksi belum ada
if (!isset($koneksi)) {
    include_once __DIR__ . '/../../backend/config/connection.php';
}

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
        
        // Simpan juga ke session agar konsisten
        $_SESSION['email'] = $email_nav;
        $_SESSION['username'] = $username_nav;
        $_SESSION['foto_profil'] = $foto_profil_nav;
    }
    $qUserNav->close();
}

// FIX: Cek foto profil di 2 kemungkinan folder (backend/uploads = lokasi baku, uploads = folder lama)
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
    padding: 16px 0;
    z-index: 1000;
}

.sidebar-menu {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.sidebar-menu a, .sidebar-bottom a {
    color: #111;
    font-size: 20px;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    text-decoration: none;
    transition: background 0.2s;
    cursor: pointer;
}

.sidebar-menu a:hover, .sidebar-bottom a:hover {
    background-color: #f0f0f0;
}

/* TOP NAVBAR (Dengan Efek Pergeseran / Shift) */
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
    transition: left 0.3s ease; /* Transisi Halus */
}

.top-navbar.shifted {
    left: 432px; /* 72px + 360px (lebar panel) */
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

/* POPUP USER DROPDOWN */
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

/* LAYOUT KONTEN UTAMA (Dengan Efek Pergeseran / Shift) */
.main-content {
    margin-left: 72px;
    margin-top: 56px;
    padding-top: 10px;
    padding-left: 24px;
    padding-right: 24px;
    transition: margin-left 0.3s ease; /* Transisi Halus */
}

.main-content.shifted {
    margin-left: 432px; /* 72px + 360px (lebar panel) */
}

.main-content > *:first-child {
    margin-top: 0 !important;
    padding-top: 0 !important;
}

/* PANEL CREATION & PANEL NOTIFIKASI */
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
    z-index: 998; /* Lebih rendah dari sidebar */
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

/* NOTIFIKASI CSS */
.notif-section-label {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 12px;
    color: #111;
}

.notif-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.notif-item {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    border-radius: 12px;
    text-decoration: none;
    color: #111;
    gap: 12px;
    transition: background 0.2s;
}

.notif-item:hover {
    background-color: #f0f0f0;
}

.notif-img-box {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    overflow: hidden;
    flex-shrink: 0;
    background-color: #e0e0e0;
}

.notif-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.notif-content {
    display: flex;
    flex-direction: column;
    flex: 1;
}

.notif-text {
    margin: 0;
    font-size: 14px;
    line-height: 1.3;
}

.notif-time {
    font-size: 12px;
    color: #767676;
    margin-top: 2px;
}
</style>

<!-- Sidebar Kiri -->
<aside class="sidebar">
    <nav class="sidebar-menu">
        <a href="/galeri_foto/index.php" title="Beranda"><i class="fa-solid fa-house"></i></a>
        <a href="#" title="Jelajahi"><i class="fa-regular fa-compass"></i></a>
        <a href="/galeri_foto/frontend/pages/profile.php" title="Kategori"><i class="fa-solid fa-table-cells"></i></a>
        <a href="javascript:void(0)" id="btnTambah" title="Buat"><i class="fa-regular fa-square-plus"></i></a>
        <a href="javascript:void(0)" id="btnNotifikasi" title="Notifikasi"><i class="fa-regular fa-bell"></i></a>
        <a href="#" title="Pesan"><i class="fa-regular fa-comment-dots"></i></a>
    </nav>
    <div class="sidebar-bottom">
        <a href="#" title="Pengaturan"><i class="fa-solid fa-gear"></i></a>
    </div>
</aside>

<!-- Top Navbar -->
<header class="top-navbar" id="topNavbar">
    <div class="search-box">
        <i class="fa-solid fa-magnifying-glass search-icon"></i>
        <input type="text" placeholder="Search your Pins">
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

<!-- INCLUDE PANEL NOTIFIKASI DARI PAGES -->
<?php include_once __DIR__ . '/../pages/notifikasi.php'; ?>

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

    // Fungsi Penggeser Konten Utama & Top Navbar
    function toggleContentShift(shift) {
        if (shift) {
            if (topNavbar) topNavbar.classList.add('shifted');
            if (mainContent) mainContent.classList.add('shifted');
        } else {
            if (topNavbar) topNavbar.classList.remove('shifted');
            if (mainContent) mainContent.classList.remove('shifted');
        }
    }

    // Toggle Panel Tambah (+)
    if (btnTambah && createPanel) {
        btnTambah.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (notificationPanel) notificationPanel.classList.remove('open');
            
            const isOpen = createPanel.classList.toggle('open');
            toggleContentShift(isOpen);
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                createPanel.classList.remove('open');
                toggleContentShift(false);
            });
        }
    }

    // Toggle Panel Notifikasi (Lonceng)
    if (btnNotifikasi && notificationPanel) {
        btnNotifikasi.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (createPanel) createPanel.classList.remove('open');

            const isOpen = notificationPanel.classList.toggle('open');
            toggleContentShift(isOpen);
        });

        if (closeNotifBtn) {
            closeNotifBtn.addEventListener('click', function() {
                notificationPanel.classList.remove('open');
                toggleContentShift(false);
            });
        }
    }

    // Menutup panel saat klik di luar area
    document.addEventListener('click', function(e) {
        let closedAny = false;
        if (createPanel && !createPanel.contains(e.target) && !btnTambah.contains(e.target)) {
            if (createPanel.classList.contains('open')) {
                createPanel.classList.remove('open');
                closedAny = true;
            }
        }
        if (notificationPanel && !notificationPanel.contains(e.target) && !btnNotifikasi.contains(e.target)) {
            if (notificationPanel.classList.contains('open')) {
                notificationPanel.classList.remove('open');
                closedAny = true;
            }
        }
        if (closedAny) {
            toggleContentShift(false);
        }
    });

    // Dropdown User Profile
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