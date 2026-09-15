<?php
session_start();

if (!isset($_SESSION['id_user'])) {
    header('Location: frontend/pages/login.php');
    exit;
}

include 'frontend/partials/header.php';
include 'frontend/partials/navbar.php';
?>

<!-- Simpan ID User untuk kebutuhan JS LocalStorage -->
<input type="hidden" id="userId" value="<?php echo $_SESSION['id_user']; ?>">

<!--<header class="top-navbar">
    <div class="search-container">
        <form action="index.php" method="GET" id="searchForm">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" name="q" id="searchInput" placeholder="Search" autocomplete="off">
            </div>
        </form>

     
        <div class="search-dropdown" id="searchDropdown">
            <div class="search-section">
                <div class="section-header">Recent searches</div>
                <div class="search-grid" id="recentSearchesGrid"></div>
            </div>

            <div class="search-section">
                <div class="section-header">Ideas for you</div>
                <div class="search-grid">
                    <a href="index.php?q=Logo+design" class="search-card">
                        <div class="card-icon"><i class="fa-solid fa-lightbulb"></i></div>
                        <span>Logo design</span>
                    </a>
                    <a href="index.php?q=Frame+template" class="search-card">
                        <div class="card-icon"><i class="fa-solid fa-image"></i></div>
                        <span>Frame template</span>
                    </a>
                    <a href="index.php?q=Pretty+landscapes" class="search-card">
                        <div class="card-icon"><i class="fa-solid fa-mountain"></i></div>
                        <span>Pretty landscapes</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="top-nav-right">
        <a href="/galeri_foto/frontend/pages/profile.php" class="profile-avatar-link" title="Profil Saya">
            <div class="profile-avatar"><?php echo strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)); ?></div>
        </a>
    </div>
</header>-->

<main class="main-content">
    <h1>Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>!</h1>
</main>


<!-- Load Script Search JS -->
<script src="frontend/partials/search.js"></script>

</body>
</html>