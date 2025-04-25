<?php
include 'koneksi.php';
session_start();

if (!isset($_SESSION['data_user'])) {
    header("Location: login.php");
    exit();
}

$id_user = $_SESSION['data_user']['id_user'];

// Ambil data user lama
$result = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$id_user'");
$data_lama = mysqli_fetch_assoc($result);
$img_lama = $data_lama['img'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $password = $_POST['password'];

    // Jika ada password baru, hash password
    $password_query = "";
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $password_query = ", password = '$hashed_password'";
    }

    // Proses upload gambar jika ada gambar baru
    $img_query = "";
    if (!empty($_FILES['img']['name'])) {
        $uploadDir = "uploads/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Buat folder jika belum ada
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif']; // Validasi format gambar
        $maxFileSize = 2 * 1024 * 1024; // Maksimal 2MB

        $fileTmpPath = $_FILES['img']['tmp_name'];
        $fileType = $_FILES['img']['type'];
        $fileSize = $_FILES['img']['size'];

        if (in_array($fileType, $allowedTypes) && $fileSize <= $maxFileSize) {
            $fileExt = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
            $img_baru = time() . "-" . uniqid() . "." . $fileExt; // Buat nama unik
            $targetFilePath = $uploadDir . $img_baru;

            // Hapus gambar lama jika ada
            if (!empty($img_lama) && file_exists($uploadDir . $img_lama)) {
                unlink($uploadDir . $img_lama);
            }

            // Simpan gambar baru
            if (move_uploaded_file($fileTmpPath, $targetFilePath)) {
                $img_query = ", img = '$img_baru'";
            } else {
                echo "Upload gambar gagal!";
                exit();
            }
        } else {
            echo "File tidak valid! Harus JPG, PNG, atau GIF dengan ukuran maks. 2MB.";
            exit();
        }
    }

    // Query update data
    $query = "UPDATE users SET 
                username = '$username', 
                nama = '$nama' 
                $password_query 
                $img_query 
              WHERE id_user = '$id_user'";

    if (mysqli_query($koneksi, $query)) {
        // Update session data setelah profil diperbarui
        $_SESSION['data_user']['username'] = $username;
        $_SESSION['data_user']['nama'] = $nama;

        if (!empty($img_query)) {
            $_SESSION['data_user']['img'] = $img_baru;
        }

        header("Location: index.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
}
?>
