<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Foto - Pinterest</title>
    
    <!-- FontAwesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { background-color: #ffffff; color: #111; overflow: hidden; }

        .main-container {
            margin-left: 72px;
            margin-top: 68px;
            width: calc(100% - 72px);
            display: flex;
            height: calc(100vh - 68px);
        }

        .create-pin-section {
            flex: 1;
            padding: 24px 40px;
            overflow-y: auto;
            border-right: 1px solid #e0e0e0;
        }

        .pin-form-grid {
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 32px;
            max-width: 900px;
        }

        .upload-card {
            background-color: #e9e9e9;
            border-radius: 24px;
            height: 400px;
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
        .form-group label { display: block; font-size: 12px; color: #767676; margin-bottom: 6px; }

        .form-control {
            width: 100%; padding: 14px 16px; border-radius: 16px;
            border: 1px solid transparent; background-color: #e9e9e9; outline: none; font-size: 14px;
        }

        .form-control:focus { border-color: #e60023; background-color: #ffffff; }
        textarea.form-control { resize: none; height: 80px; }

        .btn-publish {
            background-color: #e60023; color: #ffffff; border: none;
            padding: 12px 24px; border-radius: 24px; font-weight: 600; font-size: 14px; cursor: pointer;
        }

        .alert-error {
            padding: 12px 16px; background-color: #ffe6e6; color: #d8000c;
            border-radius: 12px; margin-bottom: 20px; font-size: 14px;
        }
    </style>
</head>
<body>

    <!-- INCLUDE NAVBAR DARI PARTIALS -->
    <?php include '../partials/navbar.php'; ?>

    <div class="main-container">
        <section class="create-pin-section">
            <h2 style="font-size: 20px; margin-bottom: 24px;">Create Pin</h2>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>

            <!-- ACTION FORM KE FOTO_PROCESS.PHP -->
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

                        <div class="form-group">
                            <label>Board Name (Optional)</label>
                            <input type="text" name="nama_board" class="form-control" placeholder="Choose or create a board">
                        </div>

                        <button type="submit" class="btn-publish">Publish</button>
                    </div>
                </div>
            </form>
        </section>
    </div>

</body>
</html>