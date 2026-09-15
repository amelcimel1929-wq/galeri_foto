<?php
session_start();
require_once __DIR__ . '/../../backend/config/connection.php';

if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit;
}

$id_user = $_SESSION['id_user'];

// Ambil daftar album milik user dari database
$queryAlbum = "SELECT * FROM album WHERE id_user = ? ORDER BY nama_album ASC";
$stmtAlbum  = $koneksi->prepare($queryAlbum);
$stmtAlbum->bind_param("i", $id_user);
$stmtAlbum->execute();
$resAlbum   = $stmtAlbum->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Foto - Pinterest</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { background-color: #ffffff; color: #111; overflow-x: hidden; }

        .main-container {
            margin-left: 100px;
            margin-top: 80px;
            padding: 20px 40px;
        }

        .create-pin-title { font-size: 24px; font-weight: 700; margin-bottom: 24px; }

        .pin-form-grid {
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 40px;
            max-width: 900px;
        }

        .upload-card {
            background-color: #e9e9e9;
            border-radius: 24px;
            height: 420px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 24px;
            position: relative;
            cursor: pointer;
        }

        .upload-card:hover { background-color: #e2e2e2; }

        .upload-icon-wrapper {
            width: 48px; height: 48px; border-radius: 50%;
            background-color: #ffffff; display: flex;
            align-items: center; justify-content: center; margin-bottom: 16px;
        }

        .file-input { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; color: #5f5f5f; margin-bottom: 8px; }

        .form-control {
            width: 100%; padding: 14px 18px; border-radius: 16px;
            border: 1px solid transparent; background-color: #e9e9e9; outline: none; font-size: 14px;
        }

        .form-control:focus { border-color: #e60023; background-color: #ffffff; }
        textarea.form-control { resize: none; height: 90px; }

        .btn-publish {
            background-color: #e60023; color: #ffffff; border: none;
            padding: 12px 28px; border-radius: 24px; font-weight: 700; font-size: 15px; cursor: pointer;
        }

        .alert-error {
            padding: 12px 18px; background-color: #ffe6e6; color: #d8000c;
            border-radius: 12px; margin-bottom: 20px; font-size: 14px;
        }
    </style>
</head>
<body>

    <?php include '../partials/navbar.php'; ?>

    <div class="main-container">
        <h2 class="create-pin-title">Create Pin</h2>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <form action="../../backend/controllers/foto_process.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload">

            <div class="pin-form-grid">
                <div>
                    <div class="upload-card">
                        <div class="upload-icon-wrapper">
                            <i class="fa-solid fa-arrow-up-from-bracket"></i>
                        </div>
                        <h4>Upload your media</h4>
                        <p style="font-size: 13px; color: #5f5f5f; margin-top: 8px;">JPG, PNG, WEBP allowed</p>
                        <input type="file" name="foto" class="file-input" required>
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="judul_foto" class="form-control" placeholder="Add a title" required>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="deskripsi_foto" class="form-control" placeholder="Add a detailed description"></textarea>
                    </div>

                    <!-- DROPDOWN ALBUM / BOARD -->
                    <div class="form-group">
                        <label for="id_album">Board / Album</label>
                        <select name="id_album" id="id_album" class="form-control" onchange="toggleAlbumInput(this)">
                            <option value="">-- Tanpa Board --</option>
                            <?php while ($album = $resAlbum->fetch_assoc()): ?>
                                <option value="<?= $album['id_album']; ?>">
                                    <?= htmlspecialchars($album['nama_album']); ?>
                                </option>
                            <?php endwhile; ?>
                            <option value="new">+ Buat Board Baru...</option>
                        </select>
                    </div>

                    <!-- INPUT NAMA BOARD BARU (MUNCUL JIKA DIPILIH 'BUAT BOARD BARU') -->
                    <div class="form-group" id="newAlbumGroup" style="display: none;">
                        <label for="nama_board_baru">Nama Board Baru</label>
                        <input type="text" name="nama_board_baru" id="nama_board_baru" class="form-control" placeholder="Masukkan nama board baru">
                    </div>

                    <button type="submit" class="btn-publish">Publish</button>
                </div>
            </div>
        </form>
    </div>

    <script>
    function toggleAlbumInput(select) {
        const newAlbumGroup = document.getElementById('newAlbumGroup');
        if (select.value === 'new') {
            newAlbumGroup.style.display = 'block';
        } else {
            newAlbumGroup.style.display = 'none';
        }
    }
    </script>
</body>
</html>
<?php $stmtAlbum->close(); ?>