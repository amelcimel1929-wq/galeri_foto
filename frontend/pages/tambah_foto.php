<input type="hidden" id="userId" value="<?php echo $_SESSION['id_user'] ?? ''; ?>">

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Galeri Foto</title>
  
  <!-- FontAwesome Icon -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    body {
      background-color: #f7f7f7;
      display: flex;
    }

    /* --- SIDEBAR KIRI --- */
    .sidebar {
      width: 72px;
      height: 100vh;
      background-color: #ffffff;
      border-right: 1px solid #e0e0e0;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      align-items: center;
      padding: 20px 0;
      position: fixed;
      left: 0;
      top: 0;
      z-index: 100;
    }

    .sidebar-menu, .sidebar-bottom {
      display: flex;
      flex-direction: column;
      gap: 16px;
      align-items: center;
      width: 100%;
    }

    .sidebar a, .sidebar-btn {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      border: none;
      background: transparent;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #111111;
      font-size: 20px;
      text-decoration: none;
      cursor: pointer;
      transition: background-color 0.2s ease;
      outline: none;
    }

    .sidebar a:hover, .sidebar-btn:hover {
      background-color: #f0f0f0;
    }

    .sidebar a.active {
      background-color: #111111;
      color: #ffffff;
    }

    /* --- TOP NAVBAR --- */
    .top-navbar {
      position: fixed;
      top: 0;
      left: 72px;
      right: 0;
      height: 68px;
      background-color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 24px;
      border-bottom: 1px solid #e0e0e0;
      z-index: 90;
    }

    .search-box {
      display: flex;
      align-items: center;
      background-color: #e9e9e9;
      border-radius: 24px;
      padding: 10px 16px;
      width: 300px;
    }

    .search-box .search-icon {
      color: #767676;
      margin-right: 10px;
    }

    .search-box input {
      border: none;
      background: transparent;
      outline: none;
      width: 100%;
      font-size: 14px;
    }

    .profile-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background-color: #e9e9e9;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      color: #111;
      text-decoration: none;
    }

    /* --- POPUP PANEL CREATE (OVERLAY DI ATAS HALAMAN) --- */
    .create-panel {
      position: fixed;
      left: 72px;
      top: 0;
      width: 320px;
      height: 100vh;
      background-color: #ffffff;
      border-right: 1px solid #e0e0e0;
      padding: 24px 16px;
      display: none; /* Disembunyikan dulu */
      flex-direction: column;
      z-index: 999; /* Muncul di paling atas tanpa mereload halaman */
      box-shadow: 4px 0 15px rgba(0, 0, 0, 0.08);
    }

    .create-panel.open {
      display: flex; /* Murni hanya kontrol visual */
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
      transition: background-color 0.2s ease, transform 0.1s ease;
      text-decoration: none;
      color: inherit;
    }

    .option-item:hover {
      background-color: #e2e2e2;
    }

    .option-item:active {
      transform: scale(0.98);
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

    .main-wrapper {
      margin-left: 72px;
      margin-top: 68px;
      padding: 24px;
      width: 100%;
    }
  </style>
</head>
<body>

  <!-- Sidebar Kiri -->
  <aside class="sidebar">
    <nav class="sidebar-menu">
      <a href="/galeri_foto/index.php" class="active" title="Beranda"><i class="fa-solid fa-house"></i></a>
      <a href="#" title="Jelajahi"><i class="fa-regular fa-compass"></i></a>
      <a href="/galeri_foto/frontend/pages/profile.php" title="Kategori"><i class="fa-solid fa-table-cells"></i></a>
      
      <!-- TOMBOL PLUS MURNI BUTTON (TIDAK AKAN MEREFRESH HALAMAN) -->
      <button type="button" class="sidebar-btn" id="createBtn" title="Buat">
        <i class="fa-regular fa-square-plus"></i>
      </button>
      
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
        <div class="profile-avatar">A</div>
      </a>
    </div>
  </header>

  <!-- POPUP PANEL CREATE -->
  <div class="create-panel" id="createPanel">
    <div class="panel-header">
      <h2 class="panel-title">Create</h2>
      <button type="button" class="close-btn" id="closeBtn">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div class="option-list">
      
      <a href="/galeri_foto/backend/pages/boards_tambah_foto.php" class="option-item">
        <div class="option-icon">
          <i class="fa-solid fa-table-cells-large"></i>
        </div>
        <div class="option-text">
          <h4>Board</h4>
          <p>Post your photos or videos and add links, stickers, effects and more</p>
        </div>
      </a>

      <a href="#" class="option-item">
        <div class="option-icon">
          <i class="fa-solid fa-scissors"></i>
        </div>
        <div class="option-text">
          <h4>Collage</h4>
          <p>Mix and match ideas to build your vision and create something new</p>
        </div>
      </a>
    </div>
  </div>

  <main class="main-wrapper">
    <!-- Konten Beranda / Home kamu di sini -->
  </main>

  <!-- JAVASCRIPT TANPA REFRESH HALAMAN -->
  <script>
    const createBtn = document.getElementById('createBtn');
    const closeBtn = document.getElementById('closeBtn');
    const createPanel = document.getElementById('createPanel');

    // Klik tombol Plus -> Buka/Tutup Menu tanpa berpindah halaman
    createBtn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      createPanel.classList.toggle('open');
    });

    // Klik Tombol Silang -> Tutup
    closeBtn.addEventListener('click', function() {
      createPanel.classList.remove('open');
    });

    // Klik di luar area menu -> Tutup otomatis
    document.addEventListener('click', function(e) {
      if (!createPanel.contains(e.target) && !createBtn.contains(e.target)) {
        createPanel.classList.remove('open');
      }
    });
  </script>
</body>
</html>