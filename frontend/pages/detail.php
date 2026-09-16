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
$uploader_id = $foto['id_user']; // ID user pemilik foto ini
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

    /* KARTU UTAMA: DIGEDEIN DIKIT LAGI (700px x 440px) */
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

    /* Foto Utama */
    .pin-photo-container {
        flex: 1.2;
        background-color: #f7f7f7;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        padding: 8px;
    }
    .pin-photo-container img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 16px;
    }

    /* Panel Informasi Kanan Dalam Kartu */
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

    /* Actions Header */
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
    .icon-btn {
        border: none;
        background: transparent;
        padding: 6px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .icon-btn:hover { background-color: #f0f0f0; }
    .icon-btn svg { width: 20px; height: 20px; fill: none; stroke: #111; stroke-width: 2; }

    .btn-save-red {
        background: #e60023;
        color: #fff;
        border: none;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 700;
        cursor: pointer;
        font-size: 13px;
    }

    /* Uploader */
    .uploader-box {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
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

    .comments-wrapper {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .comment-item { display: flex; gap: 6px; font-size: 11px; }
    .comment-user { font-weight: 700; color: #111; }

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
    }

    /* REKOMENDASI SAMPING PAS 2 FOTO */
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
</style>

<div class="main-wrapper">
    <div class="pin-top-section">
        <!-- Kartu Utama -->
        <div class="pin-main-card">
            <div class="pin-photo-container">
                <img src="<?php echo $file_path; ?>" alt="<?php echo htmlspecialchars($foto['judul_foto']); ?>">
            </div>

            <div class="pin-details-container">
                <div class="pin-scroll-content" id="scrollContainer">
                    <div class="pin-actions">
                        <div class="action-left">
                            <button id="btnLike" class="icon-btn" title="Sukai">
                                <svg id="likeIcon" viewBox="0 0 24 24">
                                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                </svg>
                            </button>
                            <span id="likeCount" style="font-weight:700; font-size:13px;">0</span>

                            <button id="btnCommentIcon" class="icon-btn" title="Komentar">
                                <svg viewBox="0 0 24 24">
                                    <path d="M12 3c5.5 0 10 3.58 10 8 0 2.8-1.8 5.25-4.5 6.75V21l-3.5-2h-2c-5.5 0-10-3.58-10-8s4.5-8 10-8z"/>
                                </svg>
                            </button>
                        </div>

                        <button class="btn-save-red">Save</button>
                    </div>

                    <div class="uploader-box">
                        <div class="avatar-circle"><?php echo strtoupper(substr($foto['username'], 0, 1)); ?></div>
                        <div>
                            <div style="font-weight:700; font-size:13px;"><?php echo htmlspecialchars($foto['nama_lengkap']); ?></div>
                            <div style="font-size:11px; color:#767676;">@<?php echo htmlspecialchars($foto['username']); ?></div>
                        </div>
                    </div>

                    <h1 class="pin-title-text"><?php echo htmlspecialchars($foto['judul_foto']); ?></h1>
                    <?php if (!empty($foto['deskripsi_foto'])): ?>
                        <div class="pin-desc-text"><?php echo nl2br(htmlspecialchars($foto['deskripsi_foto'])); ?></div>
                    <?php endif; ?>

                    <div class="comments-wrapper" id="commentsList"></div>
                </div>

                <div class="comment-input-area">
                    <input type="text" id="inputKomentar" placeholder="Add a comment..." autocomplete="off">
                    <button type="button" id="btnKirimKomentar" class="btn-send-icon">↑</button>
                </div>
            </div>
        </div>

        <!-- Rekomendasi Samping (Mengambil foto milik Uploader yang sama) -->
        <div class="pin-sidebar-recommend">
            <?php
            // Filter id_user = uploader dan id_foto != foto yang sedang dibuka
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

<script>
const ID_FOTO = <?php echo $id_foto; ?>;

document.addEventListener('DOMContentLoaded', function () {
    const btnLike = document.getElementById('btnLike');
    const likeIcon = document.getElementById('likeIcon');
    const likeCount = document.getElementById('likeCount');
    const btnCommentIcon = document.getElementById('btnCommentIcon');
    const inputKomentar = document.getElementById('inputKomentar');
    const btnKirimKomentar = document.getElementById('btnKirimKomentar');
    const commentsList = document.getElementById('commentsList');
    const scrollContainer = document.getElementById('scrollContainer');

    function checkLikeStatus() {
        fetch(`../../backend/controller/like_process.php?action=status&id_foto=${ID_FOTO}`)
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
            })
            .catch(err => console.log("Gagal load status like:", err));
    }

    btnLike.addEventListener('click', function () {
        const formData = new FormData();
        formData.append('action', 'toggle');
        formData.append('id_foto', ID_FOTO);

        fetch('../../backend/controller/like_process.php', { method: 'POST', body: formData })
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

    function loadComments() {
        fetch(`../../backend/controller/komentar_process.php?action=list&id_foto=${ID_FOTO}`)
            .then(res => res.json())
            .then(data => {
                commentsList.innerHTML = '';
                if (!data || data.length === 0) {
                    commentsList.innerHTML = '<p style="color:#767676; font-size:11px;">Belum ada komentar.</p>';
                    return;
                }
                data.forEach(item => {
                    commentsList.insertAdjacentHTML('beforeend', `
                        <div class="comment-item">
                            <div class="avatar-circle" style="width:18px; height:18px; font-size:9px; flex-shrink:0;">
                                ${item.username.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <span class="comment-user">${item.username}</span>
                                <span>${item.isi_komentar}</span>
                            </div>
                        </div>
                    `);
                });
            });
    }

    function sendComment() {
        const text = inputKomentar.value.trim();
        if (!text) return;

        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('id_foto', ID_FOTO);
        formData.append('isi_komentar', text);

        fetch('../../backend/controller/komentar_process.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'ok') {
                    inputKomentar.value = '';
                    loadComments();
                    setTimeout(() => { scrollContainer.scrollTop = scrollContainer.scrollHeight; }, 150);
                }
            });
    }

    btnKirimKomentar.addEventListener('click', sendComment);
    inputKomentar.addEventListener('keypress', e => { if (e.key === 'Enter') sendComment(); });

    checkLikeStatus();
    loadComments();
});
</script>

</body>
</html>