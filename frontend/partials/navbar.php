<input type="hidden" id="userId" value="<?php echo $_SESSION['id_user'] ?? ''; ?>">

<!-- Sidebar Kiri -->
<aside class="sidebar">
    <nav class="sidebar-menu">
        <a href="/galeri_foto/index.php" class="active" title="Beranda"><i class="fa-solid fa-house"></i></a>
        <a href="#" title="Jelajahi"><i class="fa-regular fa-compass"></i></a>
        <a href="/galeri_foto/frontend/pages/profile.php" title="Kategori"><i class="fa-solid fa-table-cells"></i></a>
        
        <!-- ROUTER DIUBAH: href ke javascript:void(0) & diberi id="btnTambah" -->
        <a href="javascript:void(0)" id="btnTambah" title="Buat"><i class="fa-regular fa-square-plus"></i></a>
        
        <a href="#" title="Notifikasi"><i class="fa-regular fa-bell"></i></a>
        <a href="#" title="Pesan"><i class="fa-regular fa-comment-dots"></i></a>
    </nav>
    <div class="sidebar-bottom">
        <a href="#" title="Pengaturan"><i class="fa-solid fa-gear"></i></a>
    </div>
</aside>

<!-- Top Navbar (Search & Profile) -->
<header class="top-navbar">
    <div class="search-box">
        <i class="fa-solid fa-magnifying-glass search-icon"></i>
        <input type="text" placeholder="Search">
    </div>
    <div class="top-nav-right">
        <!-- Avatar "A" dijadikan LINK ke Halaman Profile -->
        <a href="/galeri_foto/frontend/pages/profile.php" class="profile-avatar-link" title="Profil Saya">
            <div class="profile-avatar">A</div>
        </a>
    </div>
</header>

<!-- POP-UP CREATE (KHUSUS POPUP MENU BUKAAN) -->
<div class="create-panel" id="createPanel">
    <div class="panel-header">
        <h2 class="panel-title">Create</h2>
        <button type="button" class="close-btn" id="closeBtn">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="option-list">
        <!-- Router pindah halaman BARU AKTIF saat link di bawah ini diklik -->

        <a href="/galeri_foto/backend/pages/boards_tambah_foto.php" class="option-item">
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

<!-- CSS KHUSUS POPUP (TIDAK MENGANGGU CSS NAVBAR KAMU) -->
<style>
.create-panel {
    position: fixed;
    left: 72px; /* Disesuaikan dengan lebar sidebar kamu */
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

.create-panel.open {
    display: flex !important;
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.panel-title {
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

.option-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.option-item {
    display: flex;
    align-items: flex-start;
    padding: 12px;
    border-radius: 16px;
    background-color: #efefef;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    transition: background-color 0.2s;
}

.option-item:hover {
    background-color: #e2e2e2;
}

.option-icon {
    width: 40px;
    height: 40px;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    flex-shrink: 0;
}

.option-text h4 {
    font-size: 15px;
    font-weight: 600;
    color: #111;
    margin-bottom: 2px;
}

.option-text p {
    font-size: 12px;
    color: #5f5f5f;
    line-height: 1.3;
}
</style>

<!-- JAVASCRIPT POPUP TRIGGER -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnTambah = document.getElementById('btnTambah');
    const closeBtn = document.getElementById('closeBtn');
    const createPanel = document.getElementById('createPanel');

    if (btnTambah && createPanel) {
        // Klik tombol plus -> Murni buka pop-up tanpa router pindah halaman
        btnTambah.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            createPanel.classList.toggle('open');
        });

        // Klik tombol X
        closeBtn.addEventListener('click', function() {
            createPanel.classList.remove('open');
        });

        // Klik luar area popup untuk nutup
        document.addEventListener('click', function(e) {
            if (!createPanel.contains(e.target) && !btnTambah.contains(e.target)) {
                createPanel.classList.remove('open');
            }
        });
    }
});
</script>