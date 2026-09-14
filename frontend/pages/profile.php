<?php 
// Sertakan header dan navbar dari folder partials
include '../partials/header.php';
include '../partials/navbar.php';
?>

<main class="main-content">
    <div class="profile-header-container">
        <!-- Informasi User (Avatar besar + Nama) -->
        <div class="profile-info-section">
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

        <!-- Judul Halaman -->
        <h1 class="saved-ideas-title">Your saved ideas</h1>

        <!-- Tab Menu (Pins, Boards, Collages) -->
       <!-- Tab Menu (Liked, Boards, Collages) -->
        <div class="profile-tabs">
            <a href="#" class="tab-item">Liked</a>
            <a href="#" class="tab-item">Boards</a>
            <a href="#" class="tab-item active">Collages</a>
        </div>

        <!-- Tombol Create di Kanan -->
        <div class="create-btn-wrapper">
            <button class="btn-create-red">Create</button>
        </div>

        <!-- Sub Bagian Content (Drafts, dll) -->
        <div class="drafts-section">
            <h3>Drafts (0)</h3>
        </div>
    </div>
</main>

</body>
</html>