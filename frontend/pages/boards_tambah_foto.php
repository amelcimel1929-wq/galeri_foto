<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Pin - Pinterest</title>
    
    <!-- FontAwesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body {
            background-color: #ffffff;
            color: #111;
            overflow: hidden;
        }

        /* ==========================================
           CSS UNTUK POPUP, NAVBAR & SIDEBAR (BYPASS)
           ========================================== */
        /* Top Navbar Styling */
        header, .navbar, .top-navbar {
            position: fixed;
            top: 0;
            left: 72px;
            right: 0;
            height: 68px;
            background-color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 90;
            border-bottom: 1px solid #f0f0f0;
        }

        /* Sidebar Styling */
        aside.sidebar, .sidebar {
            width: 72px;
            height: 100vh;
            background-color: #ffffff;
            border-right: 1px solid #e0e0e0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 100;
        }

        .sidebar a, .sidebar button, .sidebar-menu a {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #111111;
            font-size: 20px;
            text-decoration: none;
            transition: background-color 0.2s;
            background: transparent;
            border: none;
            cursor: pointer;
        }

        .sidebar a:hover, .sidebar button:hover {
            background-color: #f0f0f0;
        }

        .sidebar .brand-logo, .sidebar .fa-pinterest {
            color: #e60023 !important;
            font-size: 24px;
        }

        /* Search Bar & Elements inside Navbar */
        .search-box, input[type="text"].search-input, .search-bar {
            display: flex;
            align-items: center;
            background-color: #e9e9e9;
            border-radius: 24px;
            padding: 8px 16px;
            width: 80%;
            max-width: 900px;
            border: none;
        }

        .search-box input, .search-bar input {
            border: none;
            background: transparent;
            outline: none;
            width: 100%;
            font-size: 14px;
            margin-left: 8px;
        }

        /* Profile Avatar in Navbar */
        .profile-avatar, .avatar-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #2e8b57;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border: none;
        }

        /* ==========================================
           LAYOUT KONTEN UTAMA (CREATE PIN FORM)
           ========================================== */
        .main-container {
            margin-left: 72px;
            margin-top: 68px;
            width: calc(100% - 72px);
            display: flex;
            height: calc(100vh - 68px);
        }

        /* FORM TENGAH */
        .create-pin-section {
            flex: 1;
            padding: 24px 40px;
            overflow-y: auto;
            border-right: 1px solid #e0e0e0;
        }

        .page-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 24px;
            color: #111;
        }

        .pin-form-grid {
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 32px;
            max-width: 900px;
        }

        /* Area Upload Media */
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
            cursor: pointer;
            position: relative;
            transition: background-color 0.2s;
        }

        .upload-card:hover {
            background-color: #e2e2e2;
        }

        .upload-icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background-color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .upload-icon-wrapper i {
            font-size: 20px;
            color: #111;
        }

        .upload-card h4 {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .upload-card p {
            font-size: 13px;
            color: #5f5f5f;
            line-height: 1.4;
        }

        .upload-card .file-info {
            position: absolute;
            bottom: 20px;
            font-size: 11px;
            color: #767676;
        }

        .file-input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .btn-url {
            width: 100%;
            margin-top: 16px;
            padding: 12px;
            border-radius: 24px;
            border: none;
            background-color: #e9e9e9;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
        }

        .btn-url:hover {
            background-color: #e2e2e2;
        }

        /* Form Inputs Pinterest Style */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            color: #767676;
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid transparent;
            background-color: #e9e9e9;
            outline: none;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: #e60023;
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(230, 0, 35, 0.1);
        }

        .form-control::placeholder {
            color: #767676;
        }

        textarea.form-control {
            resize: none;
            height: 80px;
        }

        /* Tombol Publish Merah */
        .btn-publish {
            background-color: #e60023;
            color: #ffffff;
            border: none;
            padding: 12px 24px;
            border-radius: 24px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.2s;
        }

        .btn-publish:hover {
            background-color: #ad081b;
        }

        /* PANEL DRAFTS (KANAN) */
        .drafts-section {
            width: 320px;
            padding: 24px 20px;
            background-color: #ffffff;
        }

        .drafts-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .drafts-header h3 {
            font-size: 16px;
            font-weight: 700;
        }

        .btn-create-new {
            width: 100%;
            padding: 12px;
            border-radius: 24px;
            border: none;
            background-color: #e9e9e9;
            font-weight: 600;
            font-size: 14px;
            color: #8e8e8e;
            cursor: not-allowed;
            margin-bottom: 24px;
        }

        .draft-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px;
            border-radius: 12px;
        }

        .draft-img {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #a855f7, #2563eb);
            flex-shrink: 0;
        }

        .draft-info {
            flex: 1;
        }

        .draft-info h5 {
            font-size: 14px;
            font-weight: 600;
            color: #111;
        }

        .draft-info p {
            font-size: 11px;
            color: #767676;
            margin-top: 2px;
        }

        .draft-more {
            color: #111;
            cursor: pointer;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <!-- MEMANGGIL NAVBAR MILIKMU -->
    <?php include '../../frontend/partials/navbar.php'; ?>

    <!-- KONTEN UTAMA CREATION TOOL -->
    <div class="main-container">
        
        <!-- FORM CREATE PIN -->
        <section class="create-pin-section">
            <h2 class="page-title">Create Pin</h2>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="pin-form-grid">
                    
                    <!-- Drop Zone Upload Media -->
                    <div>
                        <div class="upload-card">
                            <div class="upload-icon-wrapper">
                                <i class="fa-solid fa-arrow-up-from-bracket"></i>
                            </div>
                            <h4>Upload your media</h4>
                            <p>Select multiple files in your file picker with Shift or Cmd/Ctrl</p>
                            <span class="file-info">JPG, PNG up to 20MB - MP4 up to 200MB</span>
                            <input type="file" name="foto" class="file-input" required>
                        </div>
                        <button type="button" class="btn-url">Save from URL</button>
                    </div>

                    <!-- Input Detail -->
                    <div>
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="judul" class="form-control" placeholder="Tell everyone what your Pin is about" required>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="deskripsi" class="form-control" placeholder="Describe your Pin"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Link</label>
                            <input type="text" name="link" class="form-control" placeholder="Add a link">
                        </div>

                        <!--<div class="form-group">
                            <label>Board</label>
                            <select name="board" class="form-control">
                                <option value="" disabled selected>Choose a board</option>
                                <option value="1">Desain Grafis</option>
                                <option value="2">Fotografi</option>
                            </select>
                        </div>-->

                        <div class="form-group">
                            <label>Tagged topics (0)</label>
                            <input type="text" name="tags" class="form-control" placeholder="Search for a tag">
                        </div>

                        <button type="submit" class="btn-publish">Publish</button>
                    </div>

                </div>
            </form>
        </section>

        <!-- PANEL DRAFTS (KANAN) -->
        <aside class="drafts-section">
            <div class="drafts-header">
                <h3>Pin drafts (1)</h3>
                <i class="fa-solid fa-xmark" style="cursor: pointer; color: #111;"></i>
            </div>
            
            <button type="button" class="btn-create-new">Create new</button>

            <div class="draft-card">
                <div class="draft-img"></div>
                <div class="draft-info">
                    <h5>nhjg</h5>
                    <p>30 days until expiry</p>
                </div>
                <i class="fa-solid fa-ellipsis draft-more"></i>
            </div>
        </aside>

    </div>

</body>
</html>