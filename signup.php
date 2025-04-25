<?php
// Sertakan file koneksi ke database
include 'koneksi.php';

// Cek jika tombol 'signup' ditekan
if (isset($_POST['signup'])) {
    // Ambil data dari form
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $password_plain = $_POST['password'];

    if (strlen($username) < 4 || strlen($password_plain) < 4) {
        header('Location: signup.php?pesan=panjang_minimal');
        exit();
    }


    // Validasi username tidak mengandung simbol (hanya huruf dan angka)
    if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
        header('Location: signup.php?pesan=username_tidak_valid');
        exit();
    }

    // Enkripsi password menggunakan password_hash untuk keamanan
    $password = password_hash($password_plain, PASSWORD_DEFAULT);

    // Masukkan data ke dalam tabel users
    $result = mysqli_query($koneksi, "INSERT INTO users (nama, username, password) VALUES ('$nama', '$username', '$password')");

    if ($result) {
        header('Location: index.php?pesan=register_sukses');
        exit();
    } else {
        header('Location: signup.php?pesan=register_gagal');
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <!-- Import Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <!-- Container utama -->
    <div class="container d-flex align-items-center justify-content-center min-vh-100">
        <div class="col-12 col-md-6 col-lg-4">
        <?php
            if (!empty($_GET['pesan'])) {
                switch ($_GET['pesan']) {
                    case 'panjang_minimal':
            ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <strong>Oops!</strong> Username and Password must be at least 4 characters long.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
            <?php
                        break;
                    case 'username_tidak_valid':
            ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Invalid!</strong> Username must not contain any special characters or symbols.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
            <?php
                        break;
                    case 'register_gagal':
            ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Error!</strong> Registration failed. Please try again.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
            <?php
                        break;
                }
            }
            ?>
            <!-- Kartu untuk form sign in -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title text-center mb-4">Sign up</h4>

                    <!-- Form pendaftaran -->
                    <form method="post">
                        <!-- Input Nama -->
                        <div class="mb-3">
                            <label for="nama" class="form-label">Name</label>
                            <input type="text" class="form-control" name="nama" id="nama" required>
                        </div>
                        <!-- Input Username -->
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" id="username" required>
                        </div>

                        <!-- Input Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password" id="password" required>
                                <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                    <i class="fas fa-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Tombol Submit -->
                        <button type="submit" class="btn btn-primary w-100" name="signup">Sign up</button>
                    </form>

                    <br>
                    <!-- Link ke halaman login jika sudah punya akun -->
                    <p class="text-center">Already have an account? <a href="login.php">Click here to login</a></p>
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
