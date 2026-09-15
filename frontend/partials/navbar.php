<input type="hidden" id="userId" value="<?php echo $_SESSION['id_user'] ?? ''; ?>">

<!-- Sidebar Kiri -->
<aside class="sidebar">
    <nav class="sidebar-menu">
        <a href="/galeri_foto/index.php" class="active" title="Beranda"><i class="fa-solid fa-house"></i></a>
        <a href="#" title="Jelajahi"><i class="fa-regular fa-compass"></i></a>
        <a href="/galeri_foto/frontend/pages/profile.php" title="Kategori"><i class="fa-solid fa-table-cells"></i></a>
        <a href="/galeri_foto/frontend/pages/tambah_foto.php" title="Buat"><i class="fa-regular fa-square-plus"></i></a>
        <a href="#" title="Notifikasi"><i class="fa-regular fa-bell"></i></a>
        <a href="#" title="Pesan"><i class="fa-regular fa-comment-dots"></i></a>
    </nav>
    <div class="sidebar-bottom">
        <a href="#" title="Pengaturan"><i class="fa-solid fa-gear"></i></a>
    </div>
</aside>
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

<!-- Top Navbar (Search & Profile) -->
