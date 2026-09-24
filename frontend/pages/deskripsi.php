<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Koneksi dari frontend/pages/ ke backend/config/connection.php
include '../../backend/config/connection.php';

$id_user = $_SESSION['id_user'] ?? null;

if (!$id_user) {
    header('Location: ../auth/login.php');
    exit;
}

$nama_lengkap = '';
$bio          = '';
$username     = '';
$foto_profil  = '';

// Ambil data user
if (isset($koneksi) || isset($conn)) {
    $db = $koneksi ?? $conn;
    $stmt = $db->prepare("SELECT nama_lengkap, username, bio, foto_profil FROM user WHERE id_user = ?");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($data = $result->fetch_assoc()) {
        $nama_lengkap = $data['nama_lengkap'] ?? '';
        $username     = $data['username'] ?? '';
        $bio          = $data['bio'] ?? '';
        $foto_profil  = $data['foto_profil'] ?? '';
    }
    $stmt->close();
}

include '../partials/header.php';
include '../partials/navbar.php';
$avatar_src = null;
if (!empty($foto_profil)) {
    if (file_exists(__DIR__ . '/../../backend/uploads/' . $foto_profil)) $avatar_src = '../../backend/uploads/' . rawurlencode($foto_profil);
    elseif (file_exists(__DIR__ . '/../../uploads/' . $foto_profil)) $avatar_src = '../../uploads/' . rawurlencode($foto_profil);
}
?>

<style>
.edit-profile-container {
    max-width: 650px;
    margin: 80px auto 40px auto;
    padding: 20px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    color: #111111;
}

.edit-profile-title {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 8px;
}

.edit-profile-subtitle {
    font-size: 14px;
    color: #333333;
    margin-bottom: 28px;
    line-height: 1.4;
}

.field-label {
    font-size: 12px;
    font-weight: 600;
    color: #111111;
    margin-bottom: 4px;
}

.photo-section {
    margin-bottom: 24px;
}

.photo-wrapper {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-top: 8px;
}

.avatar-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background-color: #7bd6a8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    font-weight: 600;
    color: #111111;
    overflow: hidden;
}

.avatar-circle img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.btn-change-photo {
    background-color: #e9e9e9;
    border: none;
    padding: 10px 18px;
    border-radius: 24px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    color: #111111;
}

.btn-change-photo:hover {
    background-color: #d8d8d8;
}

.input-group-box {
    border: 1px solid #767676;
    border-radius: 16px;
    padding: 8px 16px 10px 16px;
    margin-bottom: 6px;
    position: relative;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.input-group-box:focus-within {
    border-color: #0084ff;
    box-shadow: 0 0 0 4px rgba(0, 132, 255, 0.2);
}

.input-group-box label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #111111;
    margin-bottom: 2px;
}

.input-group-box input,
.input-group-box textarea {
    width: 100%;
    border: none;
    outline: none;
    background: transparent;
    font-size: 16px;
    color: #111111;
    font-family: inherit;
    padding: 0;
}

.input-group-box textarea {
    resize: vertical;
    min-height: 60px;
}

.field-hint {
    font-size: 12px;
    color: #5f5f5f;
    margin-bottom: 20px;
    line-height: 1.4;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 30px;
}

.btn-reset {
    background-color: #dc3545; /* Merah */
    color: #ffffff;
    border: none;
    padding: 10px 24px;
    border-radius: 24px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}

.btn-reset:hover {
    background-color: #bb2d3b;
}

.btn-save {
    background-color: #198754; /* Hijau */
    color: #ffffff;
    border: none;
    padding: 10px 24px;
    border-radius: 24px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}

.btn-save:hover {
    background-color: #157347;
}
</style>

<div class="edit-profile-container">
    <a href="profile_pribadi.php" class="edit-profile-back" aria-label="Kembali ke profil">← Kembali ke profil</a>
    <h1 class="edit-profile-title">Edit profile</h1>
    <p class="edit-profile-subtitle">Keep your personal details private. Information you add here is visible to anyone who can view your profile.</p>

    <!-- Mengarah dari frontend/pages/ ke backend/controllers/deskripsi_process.php -->
    <form action="../../backend/controllers/deskripsi_process.php" method="POST" enctype="multipart/form-data">

        <!-- Photo -->
        <div class="photo-section">
            <div class="field-label">Photo</div>
            <div class="photo-wrapper">
                <div class="avatar-circle">
                    <?php if ($avatar_src): ?>
                        <img src="<?= htmlspecialchars($avatar_src); ?>?v=<?= time(); ?>" alt="Profile Photo">
                    <?php else: ?>
                        <?= strtoupper(substr($username ?: 'A', 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <input type="file" name="foto_profil" id="fotoInput" style="display: none;" accept="image/*">
                <button type="button" class="btn-change-photo" onclick="document.getElementById('fotoInput').click()">Change</button>
            </div>
        </div>

        <!-- Name -->
        <div class="input-group-box">
            <label for="nama_lengkap">Name</label>
            <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?= htmlspecialchars($nama_lengkap); ?>" placeholder="Add your name">
        </div>
        <div class="field-hint"></div>

        <!-- About -->
        <div class="input-group-box">
            <label for="bio">About</label>
            <textarea id="bio" name="bio" rows="3" placeholder="Tell your story"><?= htmlspecialchars($bio); ?></textarea>
        </div>
        <div class="field-hint">Tuliskan deskripsi profil singkat kamu di sini.</div>

        <!-- Username -->
        <div class="input-group-box">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($username); ?>" required>
        </div>
        <div class="field-hint">www.pinterest.com/<?= htmlspecialchars($username); ?></div>

        <!-- Buttons -->
        <div class="form-actions">
            <button type="reset" class="btn-reset">Reset</button>
            <button type="submit" class="btn-save">Save</button>
        </div>

    </form>
</div>

<style>.edit-profile-back{display:inline-flex;margin-bottom:18px;color:#111;text-decoration:none;font-weight:600}.edit-profile-back:hover{text-decoration:underline}</style>

</body>
</html>
