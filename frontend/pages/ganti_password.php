<?php
session_start();

// Cek autentikasi login
if (!isset($_SESSION['id_user'])) {
    header("Location: /galeri_foto/frontend/pages/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Kata Sandi</title>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif;
        }

        body {
            background-color: rgba(0, 0, 0, 0.4); /* Efek Modal Backdrop Blur */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* Card Ubah Kata Sandi Presisi Foto */
        .password-card {
            background-color: #ffffff;
            width: 100%;
            max-width: 440px;
            padding: 32px;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            position: relative;
        }

        .password-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .password-header h3 {
            font-size: 22px;
            font-weight: 700;
            color: #111;
        }

        .close-btn {
            background: transparent;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #5f5f5f;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .close-btn:hover {
            background-color: #f0f0f0;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        /* Input Wrapper + Ikon Mata */
        .password-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-input-wrapper input {
            width: 100%;
            padding: 12px 40px 12px 14px;
            border: 1px solid #ccc;
            border-radius: 12px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }

        .password-input-wrapper input:focus {
            border-color: #111;
        }

        .toggle-password-btn {
            position: absolute;
            right: 14px;
            cursor: pointer;
            color: #767676;
            font-size: 16px;
            transition: color 0.2s;
        }

        .toggle-password-btn:hover {
            color: #111;
        }

        .btn-submit {
            width: 100%;
            background-color: #e60023;
            color: #ffffff;
            padding: 14px;
            border: none;
            border-radius: 24px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.2s;
        }

        .btn-submit:hover {
            background-color: #ad081b;
        }
    </style>
</head>
<body>

<div class="password-card">
    <div class="password-header">
        <h3>Ubah Kata Sandi</h3>
        <a href="javascript:history.back()" class="close-btn" title="Tutup">
            <i class="fa-solid fa-xmark"></i>
        </a>
    </div>

    <form action="/galeri_foto/backend/controllers/process_ganti_password.php" method="POST">
        
        <!-- Kata Sandi Saat Ini -->
        <div class="form-group">
            <label>Kata Sandi Saat Ini</label>
            <div class="password-input-wrapper">
                <input type="password" name="current_password" required>
                <i class="fa-regular fa-eye toggle-password-btn"></i>
            </div>
        </div>

        <!-- Kata Sandi Baru -->
        <div class="form-group">
            <label>Kata Sandi Baru</label>
            <div class="password-input-wrapper">
                <input type="password" name="new_password" minlength="6" required>
                <i class="fa-regular fa-eye toggle-password-btn"></i>
            </div>
        </div>

        <!-- Konfirmasi Kata Sandi Baru -->
        <div class="form-group">
            <label>Konfirmasi Kata Sandi Baru</label>
            <div class="password-input-wrapper">
                <input type="password" name="confirm_password" minlength="6" required>
                <i class="fa-regular fa-eye toggle-password-btn"></i>
            </div>
        </div>

        <button type="submit" class="btn-submit">Simpan Password Baru</button>
    </form>
</div>

<script>
// Script Toggle Ikon Mata Password
document.querySelectorAll('.toggle-password-btn').forEach(function(icon) {
    icon.addEventListener('click', function() {
        const input = this.previousElementSibling;
        if (input.type === 'password') {
            input.type = 'text';
            this.classList.remove('fa-eye');
            this.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            this.classList.remove('fa-eye-slash');
            this.classList.add('fa-eye');
        }
    });
});
</script>

</body>
</html>