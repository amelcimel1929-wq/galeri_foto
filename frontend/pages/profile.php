<?php 
session_start();
include '../../backend/config/connection.php';

$id_user_login = $_SESSION['id_user'] ?? null;
$tab = $_GET['tab'] ?? 'collages';

// Sertakan header dan navbar dari folder partials
include '../partials/header.php';
include '../partials/navbar.php';
?>

<main class="main-content">
    <div class="profile-header-container">

        <!-- Baris atas: Judul+Tabs (kiri) sejajar dengan Profile info (kanan) -->
        <div class="profile-top-row">

            <!-- Kolom Kiri: Judul & Tabs -->
            <div class="profile-left-col">
                <h1 class="saved-ideas-title">Your saved ideas</h1>

                <div class="profile-tabs">
                    <a href="#" data-tab="liked" class="tab-item <?= $tab === 'liked' ? 'active' : '' ?>">Liked</a>
                    <a href="#" data-tab="boards" class="tab-item <?= $tab === 'boards' ? 'active' : '' ?>">Boards</a>
                    <a href="#" data-tab="collages" class="tab-item <?= $tab === 'collages' ? 'active' : '' ?>">Collages</a>
                </div>
            </div>

            <!-- Kolom Kanan: Avatar + Nama + Share -->
            <div class="profile-right-col">
                <div class="profile-avatar-large">
                    A
                    <button class="edit-avatar-btn" title="Edit Profil"><i class="fa-solid fa-pencil"></i></button>
                </div>
                <div class="profile-details">
                    <h2>amelcimel</h2>
                    <p class="following-count">0 following</p>
                </div>
                <button class="btn-share-profile">Share profile</button>
            </div>

        </div>

        <!-- INI yang bakal di-ganti-ganti pakai JS, tanpa reload halaman -->
        <div id="tab-content">
            <?php include 'get_tab_content.php'; ?>
        </div>

    </div>
</main>

<script>
document.querySelectorAll('.tab-item').forEach(tab => {
    tab.addEventListener('click', function (e) {
        e.preventDefault(); // stop link biar gak reload halaman

        const selectedTab = this.dataset.tab;

        // 1. Pindahin class active ke tab yang diklik
        document.querySelectorAll('.tab-item').forEach(t => t.classList.remove('active'));
        this.classList.add('active');

        // 2. Ambil konten baru dari server, tanpa reload
        fetch('get_tab_content.php?tab=' + selectedTab)
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