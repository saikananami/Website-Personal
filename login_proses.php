<?php 
    include "koneksi.php"; // Menghubungkan ke database
    session_start(); // Memulai sesi untuk menyimpan data user setelah login

    $username = mysqli_real_escape_string($koneksi, $_POST['username']); // Menghindari SQL Injection
    $password = $_POST['password']; // Password mentah dari input

    if (!empty($username) && !empty($password)) {
        // Ambil data user berdasarkan username dari database
        $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");
        $data = mysqli_fetch_assoc($query); // Ambil satu baris data user

        if ($data) {
            // Verifikasi password: mencocokkan password input dengan password hash di database
            if (password_verify($password, $data['password'])) {
                $_SESSION['data_user'] = $data; // Simpan data user ke session
                header("Location: index.php?pesan=login_success"); // Redirect ke halaman utama jika sukses
                exit();
            } else {
                header("Location: login.php?pesan=login_incorrect"); // Redirect jika password salah
                exit();
            }
        } else {
            header("Location: login.php?pesan=login_empty"); // Redirect jika username tidak ditemukan
            exit();
        }
    }
?>
