<?php 
session_start();
include '../../backend/config/connection.php';

$id_user_login = $_SESSION['id_user'] ?? null;

// Jika user belum login, redirect ke halaman login
if (!$id_user_login) {
    header('Location: ../auth/login.php');
    exit;
}

// Ambil tab dari URL, default ke 'boards'
$tab = $_GET['tab'] ?? 'boards';

// Sertakan header dan navbar
include '../partials/header.php';
include '../partials/navbar.php';
?>

<main class="main-content">
    <div class="profile-header-container">

        <!-- Baris atas: Judul + Tabs (kiri) & Profile info (kanan) -->
        <div class="profile-top-row">

            <!-- Kolom Kiri: Judul & Tabs -->
            <div class="profile-left-col">
                <h1 class="saved-ideas-title">Your saved ideas</h1>

                <div class="profile-tabs">
                    <a href="?tab=liked" data-tab="liked" class="tab-item <?= $tab === 'liked' ? 'active' : '' ?>">Liked</a>
                    <a href="?tab=boards" data-tab="boards" class="tab-item <?= $tab === 'boards' ? 'active' : '' ?>">Boards</a>
                    <a href="?tab=collages" data-tab="collages" class="tab-item <?= $tab === 'collages' ? 'active' : '' ?>">Collages</a>
                </div>
            </div>

            <!-- Kolom Kanan: Avatar + Nama + Share -->
            <div class="profile-right-col">
                <div class="profile-avatar-large">
                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)); ?>
                    <button class="edit-avatar-btn" title="Edit Profil"><i class="fa-solid fa-pencil"></i></button>
                </div>
                <div class="profile-details">
                    <h2><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></h2>
                    <p class="following-count">0 following</p>
                </div>
                <button class="btn-share-profile">Share profile</button>
            </div>

        </div>

        <!-- Area Dynamic Tab Content -->
        <div id="tab-content">
            <?php include 'get_tab_content.php'; ?>
        </div>

    </div>
</main>

<script>
document.querySelectorAll('.tab-item').forEach(tab => {
    tab.addEventListener('click', function (e) {
        e.preventDefault();

        const selectedTab = this.dataset.tab;

        // 1. Ubah indikator tab aktif
        document.querySelectorAll('.tab-item').forEach(t => t.classList.remove('active'));
        this.classList.add('active');

        // 2. Update URL di browser tanpa reload halaman
        window.history.pushState({}, '', '?tab=' + selectedTab);

        // 3. Ambil konten tab via AJAX (ditambahkan _t cache breaker agar data selalu fresh)
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