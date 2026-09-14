<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Masuk / Daftar — Bingkai</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../partials/style.css">
</head>
<body>

<div class="layout">

  <section class="showcase">
    <div class="frames" aria-hidden="true">
      <div class="frame f1"></div>
      <div class="frame f2"></div>
      <div class="frame f3"></div>
      <div class="frame f4"></div>
    </div>

    <div class="brand">Bingkai</div>

    <div class="showcase-copy">
      <h1>Kumpulkan ide, susun jadi koleksi.</h1>
      <p>Jelajahi dan simpan foto ke papanmu sendiri, lalu temukan kembali kapan pun kamu butuh inspirasi.</p>
    </div>

    <div class="showcase-foot">&copy; <?php echo date('Y'); ?> Bingkai</div>
  </section>

  <section class="panel">
    <div class="form-wrap">

      <input type="radio" id="tab-login" name="tab" checked>
      <input type="radio" id="tab-register" name="tab">

      <div class="tabs">
        <label for="tab-login">Masuk</label>
        <label for="tab-register">Daftar</label>
      </div>

      <div class="panes">

        <div class="pane pane-login">
          <form action="../../backend/controllers/process_login.php" method="POST">
            <div class="field">
              <label for="login-email">Email</label>
              <input type="email" id="login-email" name="email" placeholder="nama@email.com" required>
            </div>
            <div class="field">
              <label for="login-password">Kata sandi</label>
              <input type="password" id="login-password" name="password" placeholder="Kata sandi" required>
            </div>
            <button type="submit" name="login" class="submit">Masuk</button>
          </form>
          <p class="hint">Belum punya papan sendiri? Klik "Daftar" di atas.</p>
        </div>

        <div class="pane pane-register">
          <form action="../../backend/controllers/process_login.php" method="POST">
            <div class="field">
              <label for="reg-username">Username</label>
              <input type="text" id="reg-username" name="username" placeholder="Username" required>
            </div>
            <div class="field">
              <label for="reg-email">Email</label>
              <input type="email" id="reg-email" name="email" placeholder="nama@email.com" required>
            </div>
            <div class="field">
              <label for="reg-password">Kata sandi</label>
              <input type="password" id="reg-password" name="password" placeholder="Kata sandi" required>
            </div>
            <div class="field">
              <label for="reg-nama">Nama lengkap</label>
              <input type="text" id="reg-nama" name="nama_lengkap" placeholder="Nama lengkap" required>
            </div>
            <div class="field">
              <label for="reg-alamat">Alamat</label>
              <textarea id="reg-alamat" name="alamat" placeholder="Alamat" required></textarea>
            </div>
            <button type="submit" name="register" class="submit">Daftar</button>
          </form>
        </div>

      </div>
    </div>
  </section>

</div>

</body>
</html>