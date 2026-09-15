<input type="hidden" id="userId" value="<?php echo $_SESSION['id_user'] ?? ''; ?>">

<!-- FontAwesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
/* CSS NAVBAR & SIDEBAR */
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
    padding: 20px 0;
    z-index: 1000;
}

.sidebar-menu {
    display: flex;
    flex-direction: column;
    gap: 20px;
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
}

.sidebar-menu a:hover, .sidebar-bottom a:hover {
    background-color: #f0f0f0;
}

.top-navbar {
    position: fixed;
    top: 0;
    left: 72px;
    right: 0;
    height: 68px;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 24px;
    z-index: 999;
}

.search-box {
    background-color: #e9e9e9;
    border-radius: 24px;
    padding: 10px 16px;
    display: flex;
    align-items: center;
    width: 400px;
}

.search-box input {
    border: none;
    background: transparent;
    outline: none;
    margin-left: 10px;
    width: 100%;
}

.profile-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #e60023;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

/* POPUP CREATE PANEL */
.create-panel {
    position: fixed;
    left: 72px;
    top: 0;
    width: 320px;
    height: 100vh;
    background-color: #ffffff;
    border-right: 1px solid #e0e0e0;
    padding: 24px 16px;
    display: none;
    flex-direction: column;
    z-index: 9999;
    box-shadow: 4px 0 15px rgba(0, 0, 0, 0.08);
}

.create-panel.open { display: flex !important; }
.panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.panel-title { font-size: 20px; font-weight: 700; color: #111; }
.close-btn { border: none; background: transparent; font-size: 18px; cursor: pointer; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.close-btn:hover { background-color: #e9e9e9; }
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
        <a href="/galeri_foto/index.php" title="Beranda"><i class="fa-solid fa-house"></i></a>
        <a href="#" title="Jelajahi"><i class="fa-regular fa-compass"></i></a>
        <a href="/galeri_foto/frontend/pages/profile.php" title="Kategori"><i class="fa-solid fa-table-cells"></i></a>
        
        <!-- Tombol Plus untuk buka popup -->
        <a href="javascript:void(0)" id="btnTambah" title="Buat"><i class="fa-regular fa-square-plus"></i></a>
        
        <a href="#" title="Notifikasi"><i class="fa-regular fa-bell"></i></a>
        <a href="#" title="Pesan"><i class="fa-regular fa-comment-dots"></i></a>
    </nav>
    <div class="sidebar-bottom">
        <a href="#" title="Pengaturan"><i class="fa-solid fa-gear"></i></a>
    </div>
</aside>

<!-- Top Navbar -->
<header class="top-navbar">
    <div class="search-box">
        <i class="fa-solid fa-magnifying-glass search-icon"></i>
        <input type="text" placeholder="Search">
    </div>
    <div class="top-nav-right">
        <a href="/galeri_foto/frontend/pages/profile.php" class="profile-avatar-link" title="Profil Saya">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)); ?>
            </div>
        </a>
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
        <!-- ROUTER DIARAHKAN KE TAMBAH_FOTO.PHP -->
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnTambah = document.getElementById('btnTambah');
    const closeBtn = document.getElementById('closeBtn');
    const createPanel = document.getElementById('createPanel');

    if (btnTambah && createPanel) {
        btnTambah.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            createPanel.classList.toggle('open');
        });

        closeBtn.addEventListener('click', function() {
            createPanel.classList.remove('open');
        });

        document.addEventListener('click', function(e) {
            if (!createPanel.contains(e.target) && !btnTambah.contains(e.target)) {
                createPanel.classList.remove('open');
            }
        });
    }
});
</script>