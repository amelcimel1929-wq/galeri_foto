<?php
session_start();

if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../../backend/config/connection.php';
include '../partials/header.php';
include '../partials/navbar.php';

$id_foto = isset($_GET['id']) ? intval($_GET['id']) : 0;
$id_user_login = $_SESSION['id_user'];

// --- PENENTUAN URL KEMBALI DINAMIS ---
$back_url = '../../index.php'; // Default jika diakses langsung

if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
    $back_url = $_SERVER['HTTP_REFERER'];
}

// --- AMBIL DETAIL FOTO & UPLOADER ---
$query = "SELECT foto.*, user.username, user.nama_lengkap 
          FROM foto 
          JOIN user ON foto.id_user = user.id_user 
          WHERE foto.id_foto = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $id_foto);
$stmt->execute();
$foto = $stmt->get_result()->fetch_assoc();

if (!$foto) {
    echo "<p style='margin-top:100px; text-align:center;'>Foto tidak ditemukan.</p>";
    exit;
}

$file_path = "../../backend/uploads/" . htmlspecialchars($foto['lokasi_file']);
$uploader_id = $foto['id_user'];
$is_owner = ($uploader_id == $id_user_login);
$visibilitas = $foto['visibilitas'] ?? 'public';
?>

<style>
    body {
        background-color: #ffffff;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, sans-serif;
        margin: 0;
        padding-top: 75px;
    }

    .main-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 16px;
    }

    .pin-top-section {
        display: flex;
        gap: 16px;
        align-items: flex-start;
    }

    /* KARTU UTAMA */
    .pin-main-card {
        display: flex;
        width: 700px;
        height: 440px;
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 1px 12px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        flex-shrink: 0;
    }

    .pin-photo-container {
        flex: 1.2;
        background-color: #f7f7f7;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        padding: 8px;
        position: relative;
        cursor: pointer;
    }
    .pin-photo-container img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 16px;
        transition: transform 0.2s ease;
    }
    .pin-photo-container:hover img {
        transform: scale(1.02);
    }

    .btn-back-overlay {
        position: absolute;
        top: 16px;
        left: 16px;
        z-index: 10;
        background: rgba(255, 255, 255, 0.9);
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        text-decoration: none;
        transition: background-color 0.2s, transform 0.1s;
    }
    .btn-back-overlay:hover {
        background-color: #ffffff;
        transform: scale(1.08);
    }

    .pin-details-container {
        flex: 1;
        padding: 16px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        background: #fff;
    }

    .pin-scroll-content {
        overflow-y: auto;
        padding-right: 4px;
        flex-grow: 1;
    }

    .pin-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    .action-left {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .action-right {
        display: flex;
        align-items: center;
        gap: 8px;
        position: relative;
    }
    .icon-btn {
        border: none;
        background: transparent;
        padding: 8px;
        border-radius: 50%;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        color: #111111;
        transition: background-color 0.2s ease;
    }
    .icon-btn:hover { 
        background-color: #f0f0f0; 
    }

    .btn-save-red {
        background: #e60023;
        color: #fff;
        border: none;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 700;
        cursor: pointer;
        font-size: 13px;
        text-decoration: none;
        display: inline-block;
    }

    /* DROPDOWN MENU OPSI (TITIK TIGA) */
    .more-options-menu {
        display: none;
        position: absolute;
        top: 40px;
        right: 0;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        min-width: 160px;
        z-index: 100;
        overflow: hidden;
        padding: 6px 0;
    }
    .more-options-menu.active {
        display: block;
    }
    .more-options-menu button {
        width: 100%;
        text-align: left;
        padding: 10px 16px;
        background: none;
        border: none;
        font-size: 13px;
        font-weight: 600;
        color: #111;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background-color 0.2s;
    }
    .more-options-menu button:hover {
        background-color: #f0f0f0;
    }
    .more-options-menu button.danger-text {
        color: #e60023;
    }

    .uploader-box {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
        text-decoration: none;
        color: inherit;
        cursor: pointer;
        transition: opacity 0.2s ease;
    }
    .uploader-box:hover {
        opacity: 0.8;
    }

    .avatar-circle {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #333;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 12px;
    }

    .pin-title-text { font-size: 15px; font-weight: 700; margin: 0 0 6px; color: #111; line-height: 1.2; }
    .pin-desc-text { font-size: 12px; color: #333; margin-bottom: 10px; line-height: 1.3; }

    /* KOMENTAR */
    .comments-wrapper {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 10px;
    }
    .comment-item {
        display: flex;
        gap: 8px;
        font-size: 11px;
        align-items: flex-start;
    }
    .comment-body { flex: 1; min-width: 0; }
    .comment-text { line-height: 1.4; word-break: break-word; }
    .comment-user { font-weight: 700; color: #111; margin-right: 4px; }

    .comment-actions {
        margin-top: 2px;
        display: flex;
        gap: 10px;
    }
    .reply-link {
        font-size: 10px;
        font-weight: 700;
        color: #767676;
        cursor: pointer;
    }
    .reply-link:hover { color: #111; text-decoration: underline; }
    .delete-link {
        font-size: 10px;
        font-weight: 700;
        color: #e60023;
        cursor: pointer;
    }
    .delete-link:hover { text-decoration: underline; }

    .reply-box-container {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 6px;
    }
    .reply-input {
        flex: 1;
        border: 1px solid #cdcdcd;
        border-radius: 14px;
        padding: 5px 10px;
        font-size: 11px;
        outline: none;
    }
    .btn-send-icon-small {
        background: #e60023;
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        min-width: 22px;
        cursor: pointer;
        font-size: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .replies-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 8px;
        padding-left: 14px;
        border-left: 2px solid #f0f0f0;
    }

    .comment-input-area {
        display: flex;
        align-items: center;
        gap: 6px;
        padding-top: 10px;
        border-top: 1px solid #eee;
    }
    .comment-input-area input {
        flex: 1;
        border: 1px solid #cdcdcd;
        border-radius: 18px;
        padding: 8px 14px;
        font-size: 12px;
        outline: none;
    }
    .btn-send-icon {
        background: #e60023;
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        cursor: pointer;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* REKOMENDASI SAMPING */
    .pin-sidebar-recommend {
        flex: 1;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 12px;
        max-height: 440px;
        overflow-y: auto;
    }
    .recommend-item {
        border-radius: 16px;
        overflow: hidden;
        background: #f0f0f0;
    }
    .recommend-item img {
        width: 100%;
        height: auto;
        display: block;
        border-radius: 16px;
        object-fit: cover;
    }

    /* MODAL LIGHTBOX */
    .lightbox-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.85);
        backdrop-filter: blur(5px);
        justify-content: center;
        align-items: center;
    }
    .lightbox-modal.active { display: flex; }
    .lightbox-img {
        max-width: 90vw;
        max-height: 90vh;
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }
    .lightbox-close {
        position: absolute;
        top: 20px;
        right: 25px;
        color: #fff;
        font-size: 32px;
        font-weight: bold;
        cursor: pointer;
    }
</style>

<div class="main-wrapper">
    <div class="pin-top-section">
        <!-- Kartu Utama -->
        <div class="pin-main-card">
            <div class="pin-photo-container">
                <!-- href disesuaikan secara dinamis dari $back_url -->
                <a href="<?php echo htmlspecialchars($back_url); ?>" class="btn-back-overlay" title="Kembali" onclick="event.stopPropagation();">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#111111" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                </a>
                <img src="<?php echo $file_path; ?>" alt="<?php echo htmlspecialchars($foto['judul_foto']); ?>" onclick="openLightbox('<?php echo $file_path; ?>')">
            </div>

            <div class="pin-details-container">
                <div class="pin-scroll-content" id="scrollContainer">
                    <div class="pin-actions">
                        <div class="action-left">
                            <!-- LIKE -->
                            <button id="btnLike" class="icon-btn" title="Sukai">
                                <svg id="likeIcon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#111111" stroke-width="2">
                                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                </svg>
                            </button>
                            <span id="likeCount" style="font-weight:700; font-size:13px; margin-right: 4px;">0</span>

                            <!-- KOMENTAR -->
                            <button id="btnCommentIcon" class="icon-btn" title="Komentar">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#111111" stroke-width="2">
                                    <path d="M12 3c5.5 0 10 3.58 10 8 0 2.8-1.8 5.25-4.5 6.75V21l-3.5-2h-2c-5.5 0-10-3.58-10-8s4.5-8 10-8z"/>
                                </svg>
                            </button>

                            <!-- FAVORIT -->
                            <button id="btnFavorite" class="icon-btn" title="Favorit">
                                <svg id="favoriteIcon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#111111" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                                </svg>
                            </button>
                        </div>

                        <div class="action-right">
                            <a href="<?php echo $file_path; ?>" download class="btn-save-red">Save</a>

                            <?php if ($is_owner): ?>
                                <!-- TOMBOL TITIK TIGA KHUSUS PEMILIK FOTO -->
                                <button type="button" id="btnMoreOptions" class="icon-btn" title="Opsi Lainnya">
                                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                                        <circle cx="12" cy="5" r="2"/>
                                        <circle cx="12" cy="12" r="2"/>
                                        <circle cx="12" cy="19" r="2"/>
                                    </svg>
                                </button>

                                <div class="more-options-menu" id="moreOptionsMenu">
                                    <button type="button" id="btnToggleArchive">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="9" y1="9" x2="15" y2="9"></line>
                                        </svg>
                                        <span id="labelArchive"><?php echo ($visibilitas === 'private') ? 'Jadikan Public' : 'Arsipkan (Private)'; ?></span>
                                    </button>

                                    <!--<button type="button" id="btnDeleteMedia" class="danger-text">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                        Hapus Media
                                    </button>-->
                                    
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- UPLOADER BOX -->
                    <a href="profile_dimata_userlain.php?user_id=<?php echo $foto['id_user']; ?>" class="uploader-box">
                        <div class="avatar-circle"><?php echo strtoupper(substr($foto['username'], 0, 1)); ?></div>
                        <div>
                            <div style="font-weight:700; font-size:13px;"><?php echo htmlspecialchars($foto['nama_lengkap']); ?></div>
                            <div style="font-size:11px; color:#767676;">@<?php echo htmlspecialchars($foto['username']); ?></div>
                        </div>
                    </a>

                    <h1 class="pin-title-text"><?php echo htmlspecialchars($foto['judul_foto']); ?></h1>
                    <?php if (!empty($foto['deskripsi_foto'])): ?>
                        <div class="pin-desc-text"><?php echo nl2br(htmlspecialchars($foto['deskripsi_foto'])); ?></div>
                    <?php endif; ?>

                    <div style="font-weight:700; font-size:12px; margin-top:10px;">Komentar</div>
                    <div class="comments-wrapper" id="commentsList"></div>
                </div>

                <div class="comment-input-area">
                    <input type="text" id="inputKomentar" placeholder="Add a comment..." autocomplete="off">
                    <button type="button" id="btnKirimKomentar" class="btn-send-icon">↑</button>
                </div>
            </div>
        </div>

        <!-- Rekomendasi Samping -->
        <div class="pin-sidebar-recommend">
            <?php
            $rec_side = "SELECT * FROM foto WHERE id_user = ? AND id_foto != ? ORDER BY id_foto DESC LIMIT 2";
            $stmt_side = $koneksi->prepare($rec_side);
            $stmt_side->bind_param("ii", $uploader_id, $id_foto);
            $stmt_side->execute();
            $res_side = $stmt_side->get_result();

            if ($res_side->num_rows > 0):
                while ($rec = $res_side->fetch_assoc()):
                    $rec_path = "../../backend/uploads/" . htmlspecialchars($rec['lokasi_file']);
            ?>
                    <div class="recommend-item">
                        <a href="detail.php?id=<?php echo $rec['id_foto']; ?>">
                            <img src="<?php echo $rec_path; ?>" alt="<?php echo htmlspecialchars($rec['judul_foto']); ?>">
                        </a>
                    </div>
            <?php 
                endwhile;
            else:
            ?>
                <p style="color:#767676; font-size:12px; grid-column: span 2;">Tidak ada foto lain dari user ini.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL LIGHTBOX FULLSCREEN -->
<div id="lightboxModal" class="lightbox-modal" onclick="closeLightbox(event)">
    <span class="lightbox-close" onclick="closeLightbox(event)">&times;</span>
    <img id="lightboxImg" class="lightbox-img" src="" alt="Full Image">
</div>

<!-- MODAL FAVORIT -->
<div id="modalFavorit" style="display:none; position:fixed; z-index:99999; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
    <div style="background:#fff; width:320px; border-radius:16px; padding:20px; box-shadow:0 4px 20px rgba(0,0,0,0.15);">
        <h3 style="margin-top:0; font-size:16px; font-weight:700; text-align:center; color:#111;">Simpan ke Album Favorit</h3>
        
        <div id="listAlbumArea" style="max-height:180px; overflow-y:auto; margin:15px 0;"></div>

        <div style="border-top:1px solid #eee; padding-top:10px; display:flex; gap:6px;">
            <input type="text" id="inputAlbumBaru" placeholder="Nama album baru..." style="flex:1; padding:8px 12px; border:1px solid #ccc; border-radius:20px; font-size:12px; outline:none;">
            <button id="btnBuatAlbum" type="button" style="background:#e60023; color:#fff; border:none; padding:8px 12px; border-radius:20px; font-weight:bold; font-size:12px; cursor:pointer;">Buat</button>
        </div>
        
        <div style="margin-top:12px; text-align:center;">
            <button id="btnTutupModal" type="button" style="background:none; border:none; color:#767676; font-size:12px; cursor:pointer; text-decoration:underline;">Batal</button>
        </div>
    </div>
</div>

<script>
const ID_FOTO = <?php echo $id_foto; ?>;
const ID_USER_LOGIN = <?php echo (int) $id_user_login; ?>;
let VISIBILITAS_SAAT_INI = "<?php echo $visibilitas; ?>";

function openLightbox(imageSrc) {
    const modal = document.getElementById('lightboxModal');
    const modalImg = document.getElementById('lightboxImg');
    modalImg.src = imageSrc;
    modal.classList.add('active');
}

function closeLightbox(event) {
    if (event.target.id === 'lightboxModal' || event.target.classList.contains('lightbox-close')) {
        document.getElementById('lightboxModal').classList.remove('active');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const btnLike = document.getElementById('btnLike');
    const likeIcon = document.getElementById('likeIcon');
    const likeCount = document.getElementById('likeCount');
    
    const btnFavorite = document.getElementById('btnFavorite');
    const favoriteIcon = document.getElementById('favoriteIcon');
    const modalFavorit = document.getElementById('modalFavorit');
    const listAlbumArea = document.getElementById('listAlbumArea');
    const inputAlbumBaru = document.getElementById('inputAlbumBaru');
    const btnBuatAlbum = document.getElementById('btnBuatAlbum');
    const btnTutupModal = document.getElementById('btnTutupModal');

    const btnCommentIcon = document.getElementById('btnCommentIcon');
    const inputKomentar = document.getElementById('inputKomentar');
    const btnKirimKomentar = document.getElementById('btnKirimKomentar');
    const commentsList = document.getElementById('commentsList');
    const scrollContainer = document.getElementById('scrollContainer');

    // Element Titik Tiga / Menu Opsi Pemilik
    const btnMoreOptions = document.getElementById('btnMoreOptions');
    const moreOptionsMenu = document.getElementById('moreOptionsMenu');
    const btnToggleArchive = document.getElementById('btnToggleArchive');
    const labelArchive = document.getElementById('labelArchive');
    const btnDeleteMedia = document.getElementById('btnDeleteMedia');

    let isAlreadyFavorited = false;

    // --- LOGIKA TITIK TIGA & OPTION MENU ---
    if (btnMoreOptions && moreOptionsMenu) {
        btnMoreOptions.addEventListener('click', function (e) {
            e.stopPropagation();
            moreOptionsMenu.classList.toggle('active');
        });

        document.addEventListener('click', function () {
            moreOptionsMenu.classList.remove('active');
        });

        // Toggle Arsip / Visibilitas
        if (btnToggleArchive) {
            btnToggleArchive.addEventListener('click', function () {
                const targetVisibilitas = (VISIBILITAS_SAAT_INI === 'public') ? 'private' : 'public';
                const formData = new FormData();
                formData.append('action', 'update_visibilitas');
                formData.append('id_foto', ID_FOTO);
                formData.append('visibilitas', targetVisibilitas);

                fetch('../../backend/controllers/foto_process.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'ok') {
                            VISIBILITAS_SAAT_INI = targetVisibilitas;
                            if (labelArchive) {
                                labelArchive.textContent = (targetVisibilitas === 'private') ? 'Jadikan Public' : 'Arsipkan (Private)';
                            }
                            moreOptionsMenu.classList.remove('active');
                            alert('Visibilitas foto berhasil diperbarui!');
                        } else {
                            alert(data.message || 'Gagal mengubah visibilitas foto.');
                        }
                    })
                    .catch(err => {
                        console.error('Error visibilitas:', err);
                        alert('Terjadi kesalahan jaringan.');
                    });
            });
        }

        // Hapus Media
        if (btnDeleteMedia) {
            btnDeleteMedia.addEventListener('click', function () {
                if (confirm('Apakah Anda yakin ingin menghapus media ini secara permanen?')) {
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id_foto', ID_FOTO);

                    fetch('../../backend/controllers/foto_process.php', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'ok') {
                                alert('Foto berhasil dihapus.');
                                window.location.href = 'profile.php';
                            } else {
                                alert(data.message || 'Gagal menghapus foto.');
                            }
                        })
                        .catch(err => {
                            console.error('Error delete:', err);
                            alert('Terjadi kesalahan sistem saat menghapus media.');
                        });
                }
            });
        }
    }

    // --- LOGIKA FAVORIT ---
    function checkFavoriteStatus() {
        fetch('../../backend/controllers/favorit_process.php?action=status&id_foto=' + ID_FOTO)
            .then(res => res.json())
            .then(data => {
                isAlreadyFavorited = data.is_favorited;
                favoriteIcon.style.fill = data.is_favorited ? '#111111' : 'none';
            })
            .catch(err => console.log("Error status favorit:", err));
    }

    btnFavorite.addEventListener('click', function () {
        if (isAlreadyFavorited) {
            simpanFavorit(null);
        } else {
            loadAlbumList();
            modalFavorit.style.display = 'flex';
        }
    });

    btnTutupModal.addEventListener('click', function() {
        modalFavorit.style.display = 'none';
    });

    function loadAlbumList() {
        fetch('../../backend/controllers/favorit_process.php?action=get_albums')
            .then(res => res.json())
            .then(data => {
                listAlbumArea.innerHTML = '';
                if (!data.albums || data.albums.length === 0) {
                    listAlbumArea.innerHTML = '<p style="font-size:12px; color:#767676; text-align:center;">Belum ada album. Buat baru di bawah.</p>';
                    return;
                }
                data.albums.forEach(album => {
                    const item = document.createElement('div');
                    item.style.cssText = 'padding:8px 12px; background:#f0f0f0; border-radius:10px; margin-bottom:6px; cursor:pointer; font-size:13px; font-weight:600; display:flex; justify-content:space-between; align-items:center;';
                    item.innerHTML = `<span>📁 ${album.nama_album}</span> <span style="color:#e60023;">+</span>`;
                    item.onclick = function() { simpanFavorit(album.id_album_favorit); };
                    listAlbumArea.appendChild(item);
                });
            });
    }

    btnBuatAlbum.addEventListener('click', function () {
        const nama = inputAlbumBaru.value.trim();
        if (!nama) return;

        const formData = new FormData();
        formData.append('action', 'create_album');
        formData.append('nama_album', nama);

        fetch('../../backend/controllers/favorit_process.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'ok') {
                    inputAlbumBaru.value = '';
                    simpanFavorit(data.id_album_favorit);
                }
            });
    });

    function simpanFavorit(idAlbum) {
        const formData = new FormData();
        formData.append('action', 'toggle');
        formData.append('id_foto', ID_FOTO);
        if (idAlbum) formData.append('id_album_favorit', idAlbum);

        fetch('../../backend/controllers/favorit_process.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'ok') {
                    modalFavorit.style.display = 'none';
                    window.location.href = 'profile.php?tab=collages';
                }
            });
    }

    // --- LOGIKA LIKE ---
    function checkLikeStatus() {
        fetch(`../../backend/controllers/like_process.php?action=status&id_foto=${ID_FOTO}`)
            .then(res => res.json())
            .then(data => {
                likeCount.textContent = data.total_like || 0;
                if (data.is_liked) {
                    likeIcon.style.fill = '#e60023';
                    likeIcon.style.stroke = '#e60023';
                } else {
                    likeIcon.style.fill = 'none';
                    likeIcon.style.stroke = '#111111';
                }
            });
    }

    btnLike.addEventListener('click', function () {
        const formData = new FormData();
        formData.append('action', 'toggle');
        formData.append('id_foto', ID_FOTO);

        fetch('../../backend/controllers/like_process.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'ok') {
                    likeCount.textContent = data.total_like;
                    if (data.is_liked) {
                        likeIcon.style.fill = '#e60023';
                        likeIcon.style.stroke = '#e60023';
                    } else {
                        likeIcon.style.fill = 'none';
                        likeIcon.style.stroke = '#111111';
                    }
                }
            });
    });

    btnCommentIcon.addEventListener('click', function () {
        inputKomentar.focus();
        scrollContainer.scrollTop = scrollContainer.scrollHeight;
    });

    // --- LOGIKA KOMENTAR ---
    function buildCommentTree(list) {
        const map = {};
        const roots = [];
        list.forEach(item => { item.replies = []; map[item.id_komentar] = item; });
        list.forEach(item => {
            if (item.parent_id && map[item.parent_id]) {
                map[item.parent_id].replies.push(item);
            } else {
                roots.push(item);
            }
        });
        return roots;
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function renderComment(item) {
        const avatarLetter = item.username ? item.username.charAt(0).toUpperCase() : '?';
        const isOwner = item.id_user === ID_USER_LOGIN;
        let repliesHtml = '';
        if (item.replies && item.replies.length > 0) {
            repliesHtml = `<div class="replies-list">${item.replies.map(renderComment).join('')}</div>`;
        }

        return `
            <div class="comment-item" data-id="${item.id_komentar}">
                <div class="avatar-circle" style="width:18px; height:18px; font-size:9px; flex-shrink:0;">${avatarLetter}</div>
                <div class="comment-body">
                    <div class="comment-text">
                        <span class="comment-user">${item.username}</span>
                        <span>${item.isi_komentar}</span>
                    </div>
                    <div class="comment-actions">
                        <span class="reply-link" data-id="${item.id_komentar}" data-username="${escapeHtml(item.username)}">Balas</span>
                        ${isOwner ? `<span class="delete-link" data-id="${item.id_komentar}">Hapus</span>` : ''}
                    </div>
                    <div class="reply-box-container" id="replyBox-${item.id_komentar}" style="display:none;">
                        <input type="text" class="reply-input" placeholder="Balas ke ${escapeHtml(item.username)}...">
                        <button type="button" class="btn-send-icon-small" data-parent="${item.id_komentar}">↑</button>
                    </div>
                    ${repliesHtml}
                </div>
            </div>
        `;
    }

    function loadComments() {
        fetch(`../../backend/controllers/komentar_process.php?action=list&id_foto=${ID_FOTO}`)
            .then(res => res.json())
            .then(data => {
                commentsList.innerHTML = '';
                if (!Array.isArray(data) || data.length === 0) {
                    commentsList.innerHTML = '<p style="color:#767676; font-size:11px;">Belum ada komentar.</p>';
                    return;
                }
                const tree = buildCommentTree(data);
                commentsList.innerHTML = tree.map(renderComment).join('');
            });
    }

    function sendComment(text, parentId = null) {
        const isiTrim = text.trim();
        if (!isiTrim) return;

        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('id_foto', ID_FOTO);
        formData.append('isi_komentar', isiTrim);
        if (parentId) formData.append('parent_id', parentId);

        fetch('../../backend/controllers/komentar_process.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'ok') {
                    loadComments();
                    setTimeout(() => { scrollContainer.scrollTop = scrollContainer.scrollHeight; }, 150);
                }
            });
    }

    btnKirimKomentar.addEventListener('click', function () {
        sendComment(inputKomentar.value);
        inputKomentar.value = '';
    });
    inputKomentar.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            sendComment(inputKomentar.value);
            inputKomentar.value = '';
        }
    });

    commentsList.addEventListener('click', function (e) {
        const replyLink = e.target.closest('.reply-link');
        if (replyLink) {
            const id = replyLink.dataset.id;
            const box = document.getElementById(`replyBox-${id}`);
            if (box) {
                const isOpen = box.style.display === 'flex';
                document.querySelectorAll('.reply-box-container').forEach(b => b.style.display = 'none');
                box.style.display = isOpen ? 'none' : 'flex';
                if (!isOpen) {
                    const inp = box.querySelector('.reply-input');
                    if (inp) inp.focus();
                }
            }
            return;
        }

        const deleteLink = e.target.closest('.delete-link');
        if (deleteLink) {
            if (confirm('Hapus komentar ini?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id_komentar', deleteLink.dataset.id);
                fetch('../../backend/controllers/hapus_komentar_process.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => { if (data.status === 'ok') loadComments(); });
            }
            return;
        }

        const sendBtn = e.target.closest('.btn-send-icon-small');
        if (sendBtn) {
            const parentId = sendBtn.dataset.parent;
            const box = document.getElementById(`replyBox-${parentId}`);
            const inp = box ? box.querySelector('.reply-input') : null;
            if (inp) {
                sendComment(inp.value, parentId);
                inp.value = '';
            }
        }
    });

    // Inisialisasi
    checkLikeStatus();
    checkFavoriteStatus();
    loadComments();
});
</script>

</body>
</html>