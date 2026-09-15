<?php

session_start();

require_once __DIR__ . '/../config/connection.php';

if (!isset($_SESSION['id_user'])) {
    header('Location: ../../frontend/pages/login.php');
    exit;
}

$id_user = $_SESSION['id_user'];

$action = $_POST['action'] ?? $_GET['action'] ?? '';


// =========================
// UPLOAD FOTO
// =========================

if (
    $action === 'upload' &&
    $_SERVER['REQUEST_METHOD'] === 'POST'
) {

    $judul_foto = trim($_POST['judul_foto'] ?? '');

    $deskripsi_foto = trim(
        $_POST['deskripsi_foto'] ?? ''
    );

    $nama_board = trim(
        $_POST['nama_board'] ?? ''
    );

    $file = $_FILES['foto'] ?? null;


    if (
        empty($judul_foto) ||
        !$file ||
        empty($file['name'])
    ) {

        header(
            'Location: ../../index.php?error=' .
            urlencode('Judul dan gambar wajib diisi')
        );

        exit;
    }


    if ($file['error'] !== UPLOAD_ERR_OK) {

        header(
            'Location: ../../index.php?error=' .
            urlencode('Gagal membaca file gambar')
        );

        exit;
    }


    $ekstensi_diizinkan = [
        'jpg',
        'jpeg',
        'png',
        'webp',
        'gif'
    ];

    $ekstensi = strtolower(
        pathinfo(
            $file['name'],
            PATHINFO_EXTENSION
        )
    );


    if (!in_array($ekstensi, $ekstensi_diizinkan)) {

        header(
            'Location: ../../index.php?error=' .
            urlencode('Format gambar tidak didukung')
        );

        exit;
    }


    // FOLDER UPLOAD

    $folder_upload = __DIR__ . '/../uploads/';


    if (!is_dir($folder_upload)) {

        mkdir(
            $folder_upload,
            0777,
            true
        );

    }


    // NAMA FILE BARU

    $nama_file =
        'foto_' .
        uniqid() .
        '_' .
        time() .
        '.' .
        $ekstensi;


    $tujuan = $folder_upload . $nama_file;


    // PINDAHKAN FILE

    if (
        move_uploaded_file(
            $file['tmp_name'],
            $tujuan
        )
    ) {


        // SIMPAN FOTO KE DATABASE

        $stmt = $koneksi->prepare(

            "INSERT INTO foto
            (
                id_user,
                judul_foto,
                deskripsi_foto,
                tanggal_unggahan,
                lokasi_file
            )
            VALUES (?, ?, ?, NOW(), ?)"

        );


        $stmt->execute([

            $id_user,
            $judul_foto,
            $deskripsi_foto,
            $nama_file

        ]);


        header('Location: ../../index.php');

        exit;

    }


    header(
        'Location: ../../index.php?error=' .
        urlencode('Gagal mengunggah gambar')
    );

    exit;

}