<?php
include 'koneksi.php'; // Menghubungkan file ini dengan koneksi ke database
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-light"> <!-- Memberi latar belakang terang -->

    <div class="container d-flex align-items-center justify-content-center min-vh-100"> <!-- Container untuk pusatkan konten -->
        <div class="col-12 col-md-6 col-lg-4"> <!-- Ukuran responsif -->

            <?php
            // Mengecek apakah terdapat pesan di URL (seperti pesan error atau logout)
            if (!empty($_GET['pesan'])) {
                switch ($_GET['pesan']) {
                    case 'login_empty':
            ?>
                        <!-- Alert jika username/password kosong atau tidak ditemukan -->
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Sorry!</strong> Username or Password Not Found
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
            <?php
                        break;
                    case 'login_incorrect':
            ?>
                        <!-- Alert jika username/password salah -->
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Sorry!</strong> Username or Password Incorrect
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
            <?php
                        break;
                    case 'logout_success':
            ?>
                        <!-- Alert jika berhasil logout -->
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>Congrats!</strong> You're Successfully Logout
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
            <?php
                        break;
                }
            }
            ?>

            <div class="card shadow-sm"> <!-- Kartu form login -->
                <div class="card-body">
                    <h4 class="card-title text-center mb-4">Login</h4>
                    <form action="login_proses.php" method="post"> <!-- Form kirim ke file login_proses.php -->
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" id="username" required> <!-- Input username -->
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password" id="password" required>
                                <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                    <i class="fas fa-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button> <!-- Tombol login -->
                    </form>
                    <br>
                    <p class="text-center">Don't have an account yet? <a href="signup.php">Sign Up</a></p> <!-- Link ke halaman registrasi -->
                </div>
            </div>
        </div>
    </div>

    <!-- Script Bootstrap untuk interaktivitas -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', function () {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            eyeIcon.classList.toggle('fa-eye');
            eyeIcon.classList.toggle('fa-eye-slash');
        });
    </script>
</body>

</html>
