<?php
session_start();

if (!isset($_SESSION['id_user'])) {
    header('Location: frontend/pages/login.php');
    exit;
}

require_once __DIR__ . '/backend/config/connection.php';
include 'frontend/partials/header.php';
include 'frontend/partials/navbar.php';
?>

<style>
    .pin-container {
        column-count: 5;
        column-gap: 16px;
        padding: 20px 32px;
        margin-top: 70px;
    }
    .pin-card {
        break-inside: avoid;
        margin-bottom: 16px;
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        background-color: #f0f0f0;
    }
    .pin-card img {
        width: 100%;
        display: block;
        border-radius: 16px;
        transition: filter 0.2s ease;
    }
    .pin-card:hover img {
        filter: brightness(0.85);
    }
    .pin-info {
        padding: 10px;
        font-weight: 600;
        font-size: 14px;
        color: #111;
    }
    @media (max-width: 1200px) { .pin-container { column-count: 4; } }
    @media (max-width: 800px)  { .pin-container { column-count: 2; } }
</style>

<input type="hidden" id="userId" value="<?php echo $_SESSION['id_user']; ?>">

<main class="main-content">
    <div class="pin-container">
        <?php
        // PERBAIKAN: Ganti tanggal_unggahan menjadi tanggal_ungahan (menggunakan 1 huruf g)
        $query = "SELECT * FROM foto ORDER BY tanggal_ungahan DESC";
        $stmt  = $koneksi->prepare($query);

        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
        ?>
                    <div class="pin-card">
                        <!-- Memanggil gambar dari folder backend/uploads/ -->
                        <img src="backend/uploads/<?php echo htmlspecialchars($row['lokasi_file']); ?>" 
                             alt="<?php echo htmlspecialchars($row['judul_foto']); ?>">
                        <div class="pin-info">
                            <?php echo htmlspecialchars($row['judul_foto']); ?>
                        </div>
                    </div>
        <?php 
                endwhile;
            else:
        ?>
                <p style="text-align: center; color: #767676; grid-column: 1/-1;">Belum ada foto yang diunggah.</p>
        <?php 
            endif;
            $stmt->close();
        }
        ?>
    </div>
</main>

<script src="frontend/partials/search.js"></script>
</body>
</html>