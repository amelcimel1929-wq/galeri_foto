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
$uploader_id = $foto['id_user'];
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
        gap: 2px;
    }
    .icon-btn {
        border: none;
        background: transparent;
        padding: 4px;
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
        text-decoration: none;
        display: inline-block;
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

    /* ===== KOMENTAR & BALASAN ===== */
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

    /* Balasan ditaruh dalam wadah sendiri supaya rapi & terindentasi */
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

                        <a href="<?php echo $file_path; ?>" download class="btn-save-red">Save</a>
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

<script>
const ID_FOTO = <?php echo $id_foto; ?>;
const ID_USER_LOGIN = <?php echo (int) $id_user_login; ?>;

document.addEventListener('DOMContentLoaded', function () {
    const btnLike = document.getElementById('btnLike');
    const likeIcon = document.getElementById('likeIcon');
    const likeCount = document.getElementById('likeCount');
    const btnCommentIcon = document.getElementById('btnCommentIcon');
    const inputKomentar = document.getElementById('inputKomentar');
    const btnKirimKomentar = document.getElementById('btnKirimKomentar');
    const commentsList = document.getElementById('commentsList');
    const scrollContainer = document.getElementById('scrollContainer');

    // ---------- LIKE (tidak berubah) ----------
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
            })
            .catch(err => console.log("Gagal load status like:", err));
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
            })
            .catch(err => console.log("Gagal toggle like:", err));
    });

    btnCommentIcon.addEventListener('click', function () {
        inputKomentar.focus();
        scrollContainer.scrollTop = scrollContainer.scrollHeight;
    });

    // ---------- KOMENTAR + BALASAN (BERTINGKAT, ala TikTok) ----------

    function buildCommentTree(list) {
        const map = {};
        const roots = [];

        list.forEach(item => {
            item.replies = [];
            map[item.id_komentar] = item;
        });

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

    // Render satu komentar + seluruh balasannya (rekursif)
    function renderComment(item) {
        const avatarLetter = item.username ? item.username.charAt(0).toUpperCase() : '?';
        const isOwner = item.id_user === ID_USER_LOGIN;

        let repliesHtml = '';
        if (item.replies && item.replies.length > 0) {
            repliesHtml = `<div class="replies-list">${item.replies.map(renderComment).join('')}</div>`;
        }

        return `
            <div class="comment-item" data-id="${item.id_komentar}">
                <div class="avatar-circle" style="width:18px; height:18px; font-size:9px; flex-shrink:0;">
                    ${avatarLetter}
                </div>
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
            })
            .catch(err => console.log("Gagal load komentar:", err));
    }

    // Kirim komentar baru ATAU balasan (kalau parentId diisi)
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
                } else if (data.message) {
                    alert(data.message);
                }
            })
            .catch(err => console.log("Gagal kirim komentar:", err));
    }

    // Hapus komentar (balasannya ikut terhapus di server via ON DELETE CASCADE)
    function deleteComment(idKomentar) {
        if (!confirm('Hapus komentar ini?')) return;

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id_komentar', idKomentar);

        fetch('../../backend/controllers/hapus_komentar_process.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'ok') {
                    loadComments();
                } else if (data.message) {
                    alert(data.message);
                }
            })
            .catch(err => console.log("Gagal hapus komentar:", err));
    }

    // Komentar utama (bukan balasan)
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

    // Event delegation: tombol Balas, Hapus, dan kirim balasan
    commentsList.addEventListener('click', function (e) {
        // Klik "Balas" -> tampilkan / sembunyikan kotak input balasan
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

        // Klik "Hapus"
        const deleteLink = e.target.closest('.delete-link');
        if (deleteLink) {
            deleteComment(deleteLink.dataset.id);
            return;
        }

        // Klik tombol kirim di dalam kotak balasan
        const sendBtn = e.target.closest('.btn-send-icon-small');
        if (sendBtn) {
            const parentId = sendBtn.dataset.parent;
            const box = document.getElementById(`replyBox-${parentId}`);
            const inp = box ? box.querySelector('.reply-input') : null;
            if (inp) {
                sendComment(inp.value, parentId);
                inp.value = '';
            }
            return;
        }
    });

    // Tekan Enter di dalam kotak balasan juga mengirim
    commentsList.addEventListener('keypress', function (e) {
        if (e.key === 'Enter' && e.target.classList.contains('reply-input')) {
            const box = e.target.closest('.reply-box-container');
            const sendBtn = box.querySelector('.btn-send-icon-small');
            const parentId = sendBtn.dataset.parent;
            sendComment(e.target.value, parentId);
            e.target.value = '';
        }
    });

    checkLikeStatus();
    loadComments();
});
</script>

</body>
</html>