<?php
session_start();

if (!isset($_SESSION['id_user'])) {
    header('Location: frontend/pages/login.php');
    exit;
}

require_once __DIR__ . '/backend/config/connection.php';
include 'frontend/partials/header.php';
include 'frontend/partials/navbar.php';

$id_user_login = $_SESSION['id_user'];
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
    
    /* Overlay Hover */
    .pin-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0, 0, 0, 0.2);
        opacity: 0;
        transition: opacity 0.2s ease;
        display: flex;
        justify-content: flex-end;
        align-items: flex-end;
        padding: 12px;
        box-sizing: border-box;
        pointer-events: none;
    }
    .pin-card:hover .pin-overlay {
        opacity: 1;
    }
    
    /* Tombol Unduh SVG */
    .btn-download-icon {
        pointer-events: auto;
        background: #ffffff;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.25);
        transition: transform 0.15s ease, background-color 0.15s ease;
    }
    .btn-download-icon:hover {
        transform: scale(1.08);
        background-color: #f0f0f0;
    }
    .btn-download-icon svg {
        width: 18px;
        height: 18px;
        fill: #111111;
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

<main class="main-content">
    <div class="pin-container">
        <?php
        // Query hanya mengambil foto publik ATAU foto privat milik user yang sedang login
        $query = "SELECT * FROM foto 
                  WHERE visibilitas = 'public' OR (visibilitas = 'private' AND id_user = ?) 
                  ORDER BY tanggal_ungahan DESC";
                  
        $stmt = $koneksi->prepare($query);

        if ($stmt) {
            $stmt->bind_param("i", $id_user_login);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
                    $id_foto = $row['id_foto'];
                    $file_path = "backend/uploads/" . htmlspecialchars($row['lokasi_file']);
        ?>
                    <div class="pin-card">
                        <!-- Link menuju halaman detail -->
                        <a href="frontend/pages/detail.php?id=<?php echo $id_foto; ?>" style="display: block; text-decoration: none;">
                            <img src="<?php echo $file_path; ?>" alt="<?php echo htmlspecialchars($row['judul_foto']); ?>">
                            
                            <div class="pin-overlay">
                                <!-- Tombol Unduh SVG -->
                                <a href="<?php echo $file_path; ?>" download="<?php echo htmlspecialchars($row['judul_foto']); ?>" class="btn-download-icon" title="Unduh Gambar" onclick="event.stopPropagation();">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M12 15.586l-4.293-4.293-1.414 1.414L12 18.414l5.707-5.707-1.414-1.414L12 15.586z"/>
                                        <path d="M11 3h2v13h-2z"/>
                                        <path d="M5 20h14v2H5z"/>
                                    </svg>
                                </a>
                            </div>
                        </a>
                        <div class="pin-info">
                            <?php echo htmlspecialchars($row['judul_foto']); ?>
                        </div>
                    </div>
        <?php 
                endwhile;
            else:
        ?>
                <p style="text-align: center; color: #767676; grid-column: 1/-1;">Belum ada foto yang ditampilkan.</p>
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