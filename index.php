<?php
    include 'koneksi.php';

    //login terlebih dahulu
    session_start();
    if(!isset($_SESSION['data_user'])) {
        header("Location: login.php");
        exit();
    }
    $userData = $_SESSION['data_user'];  // Mengambil data user yang sudah login
    $img = !empty($userData['img']) ? "uploads/" . $userData['img'] : "assets/img/default.png"; // Jika ada gambar profil, gunakan gambar tersebut, jika tidak, gunakan gambar default
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Go-List</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <link  href="css/styles.css" rel="stylesheet">
        <link  href="css/customs.css" rel="stylesheet">
    </head>
    <body class="d-flex flex-column min-vh-100">
    <!-- Navbar Section -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark" style="background: #0d6efd;">
        <a class="navbar-brand ps-3" href="#">Go-List</a>
        <!-- Sidebar Toggle Button -->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <div class="ms-auto"></div>
            <li class="nav-item dropdown">
                <!-- User Profile Dropdown -->
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php
                        // Mengambil gambar profil pengguna jika ada, jika tidak gunakan gambar default
                        $profileImg = !empty($_SESSION['data_user']['img']) ? "uploads/" . $_SESSION['data_user']['img'] : "default.png";
                    ?>
                    <!-- Menampilkan gambar profil -->
                    <img src="<?= htmlspecialchars($profileImg); ?>" alt="Profile Picture" class="rounded-circle" width="40" height="40">
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editProfileModal">User Profile</a></li> <!-- Tautan ke halaman profil pengguna -->
                    <li><hr class="dropdown-divider" /></li>
                    <li><a class="dropdown-item" href="logout.php">Logout</a></li> <!-- Tautan untuk logout -->
                </ul>
            </li>
        </ul>
    </nav>

    <!-- Layout for Sidebar and Main Content -->
    <div id="layoutSidenav">
        <!-- Sidebar Section -->
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark bg-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <!-- Heading for Navigation -->
                        <div class="sb-sidenav-menu-heading">Navigasi</div>
                            <!-- Menu Umum untuk Semua Pengguna -->
                            <a class="nav-link" href="index.php">
                                <div class="sb-nav-link-icon"><i class="fa-solid fa-table-list"></i></div>
                                Tasks
                            </a>

                            <a class="nav-link" href="index.php?page=dashboard.php">
                                <div class="sb-nav-link-icon"><i class="fa-solid fa-table-columns"></i></div>
                                Dashboard
                            </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer bg-primary text-white">
                    <div class="small">Logged in as:</div>
                    <?php echo $_SESSION['data_user']['username']; ?> <!-- Menampilkan nama pengguna yang sedang login -->
                </div>
            </nav>
        </div>

        <!-- Main Content Section -->
        <div id="layoutSidenav_content">
            <main>
                <!-- Menampilkan pesan berdasarkan parameter GET (seperti login success atau error) -->
                <?php
                    if (!empty($_GET['pesan'])) {
                        switch ($_GET['pesan']) {
                            case 'login_success':
                ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Welcome!</strong> <?php echo $_SESSION['data_user']['username'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php
                    break;
                    case 'login_incorrect':
                ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Sorry!</strong> Username Or Password Wrong
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php
                    break;
                    }
                    }
                ?>
                <!-- Menampilkan pesan tambahan jika tersedia di session -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show mt-3 mx-3" role="alert">
                        <strong><?= $_SESSION['message_title'] ?></strong> <?= $_SESSION['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['message'], $_SESSION['message_type'], $_SESSION['message_title']); ?>
                <?php endif; ?>

                <!-- Memasukkan halaman dinamis berdasarkan parameter GET (default: tasks.php) -->
                <?php
                    $page = isset($_GET['page']) ? $_GET['page'] : 'tasks.php'; // Mengatur halaman yang akan dimuat
                    include $page; // Memasukkan halaman sesuai parameter 'page'
                ?>
                <!-- Modal Edit Profile -->
                <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content rounded-4 shadow-sm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="user_profile_edit.php" method="POST" enctype="multipart/form-data">
                        <div class="modal-body">

                        <div class="profile-container mb-1">
                            <img src="<?= htmlspecialchars($img); ?>" alt="Profile Picture" class="profile-img">
                        </div>

                        <div class="mb-2">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" value="<?= $_SESSION['data_user']['username'] ?>" required>
                        </div>

                        <div class="mb-2">
                            <label for="nama" class="form-label">Name</label>
                            <input type="text" class="form-control" name="nama" value="<?= $_SESSION['data_user']['nama'] ?>" required>
                        </div>

                        <div class="mb-2">
                            <label for="password" class="form-label">New Password (optional)</label>
                            <input type="password" class="form-control" name="password">
                        </div>

                        <div class="mb-2">
                            <label for="img" class="form-label">Profile Picture</label>
                            <input type="file" class="form-control" name="img" accept="image/*">
                        </div>
                        </div>

                        <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                    
                    </div>
                </div>
                </div>

            </main>
            <!-- Footer Section -->
            <footer class="bg-body-tertiary text-center text-lg-start py-3 border-top mt-auto">
                <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <p class="mb-2 mb-md-0 text-muted">© 2025 To-Do List Go-List. All Rights Reserved.</p>
                    <div class="social-media">
                        <!-- Ikon media sosial -->
                        <a href="#" class="text-muted me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-muted me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-muted"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>

        // Menunggu sampai document selesai dimuat
        $(document).ready(function () {
            // Ketika tombol dengan id 'toggleForm' diklik
            $("#toggleForm").click(function () {
                // Efek slide untuk menampilkan atau menyembunyikan form task
                $("#taskForm").slideToggle(300); // Efek slide
            });
        });

        // Menunggu sampai document selesai dimuat
        $(document).ready(function () {
            // Menambahkan event listener untuk tombol dengan class 'toggle-filter'
            $('.toggle-filter').on('click', function () {
                // Mendapatkan ID target filter yang terkait dengan tombol yang diklik
                var targetId = $(this).attr('for');
                // Menampilkan atau menyembunyikan filter yang sesuai dengan targetId
                $('#' + targetId).slideToggle(200); // Efek slide
            });
        });

        // Logika untuk toggle sidebar
        document.getElementById("sidebarToggle").addEventListener("click", function () {
            // Menambahkan atau menghapus class 'sb-sidenav-toggled' pada body untuk toggle sidebar
            document.body.classList.toggle("sb-sidenav-toggled");
        });

        // Menunggu sampai document selesai dimuat
        $(document).ready(function () {
            let page = 0; // Variabel untuk melacak halaman saat melakukan infinite scroll

            // Fungsi untuk mengambil data task berdasarkan filter yang diterapkan
            function fetchData(reset = false) {
                // Mengambil nilai dari masing-masing filter yang dipilih
                const filtersearch = $('#filtersearch').val();
                const priority = $('#filterpriority').val();
                const status = $('#filterstatus').val();
                const dateFilter = $('#filterdate').val();
                const timeOrder = $('#filtertime').val();
                const nameOrder = $('#filtername').val();

                // Jika reset = true, halaman akan diset ke 0 (mengulang pencarian)
                if (reset) page = 0;

                // Menampilkan animasi loading
                $('#loading').show();

                // Melakukan permintaan AJAX untuk mengambil data task yang difilter
                $.ajax({
                    url: "action.php", // URL untuk melakukan request
                    type: "POST", // Metode POST
                    data: {
                        action: "filterTasks", // Tindakan untuk memfilter task
                        page: page, // Halaman yang sedang ditampilkan
                        filtersearch: filtersearch, // Filter berdasarkan pencarian
                        priority: priority, // Filter berdasarkan prioritas
                        status: status, // Filter berdasarkan status
                        filterdate: dateFilter, // Filter berdasarkan tanggal
                        filtertime: timeOrder, // Filter berdasarkan waktu
                        filtername: nameOrder // Filter berdasarkan nama
                    },
                    success: function (data) {
                        // Jika reset = true, ganti konten table dengan data baru
                        if (reset) {
                            $('#task-list tbody').html(data);
                        } else {
                            // Jika reset = false, append data baru ke dalam table
                            $('#task-list tbody').append(data);
                        }

                        // Cek apakah tidak ada task yang ditemukan atau tidak ada task lagi
                        if (data.includes("No tasks found") || data.includes("No more tasks")) {
                            $('#loadMoreBtn').hide(); // Sembunyikan tombol "Load More"
                        } else {
                            $('#loadMoreBtn').show(); // Tampilkan tombol "Load More"
                        }

                        // Panggil fungsi untuk rebind checkbox logic setelah task ditambahkan
                        rebindCheckboxLogic();

                        // Sembunyikan animasi loading setelah data selesai dimuat
                        $('#loading').hide();
                    },
                    error: function (xhr, status, error) {
                        console.error("Terjadi kesalahan saat memuat task: ", error);
                        // Sembunyikan animasi loading jika terjadi error
                        $('#loading').hide();
                    }
                });
            }
            // Ketika tombol "Load More" diklik, increment halaman dan ambil data berikutnya
            $('#loadMoreBtn').click(function () {
                page++; // Increment halaman
                fetchData(false); // Ambil data tanpa mereset halaman
            });

            // Ketika salah satu filter diubah, muat data baru berdasarkan filter tersebut
            $('#filterpriority, #filterstatus, #filterdate, #filtertime, #filtername').on('change', function () {
                fetchData(true); // Reset dan ambil data berdasarkan filter baru
            });

            // Ketika form pencarian disubmit (Enter atau klik tombol search)
            $('#searchForm').on('submit', function (e) {
                e.preventDefault(); // Cegah reload halaman
                fetchData(true); // Ambil data berdasarkan kata kunci pencarian
            });

            // Muat pertama kali setelah halaman siap
            fetchData(true);
        });

        let isAllSelected = false; // Status apakah semua checkbox terpilih
        let selectedIDs = new Set(); // Set untuk menyimpan ID task yang dipilih

        document.addEventListener("DOMContentLoaded", function () {
            const selectAllBtn = document.getElementById('selectAllBtn'); // Tombol 'select all'
            const deleteBtn = document.getElementById('bulkDeleteBtn'); // Tombol hapus
            const bulkIdsInput = document.getElementById('bulk_ids'); // Input hidden untuk simpan ID
            const selectedCount = document.getElementById('selectedCount'); // Menampilkan jumlah yang dipilih

            // Saat tombol 'select all' diklik
            selectAllBtn.addEventListener('click', () => {
                isAllSelected = !isAllSelected;

                if (isAllSelected) {
                    // Ambil filter dari form jika ada filter yang digunakan
                    const filterData = new URLSearchParams({
                        filtersearch: $('#filtersearch').val(),
                        priority: $('#filterpriority').val(),
                        status: $('#filterstatus').val(),
                        filtertime: $('#filtertime').val(),
                    });

                    // Kirim request ke server untuk ambil semua ID task yang sesuai filter
                    fetch('get_all_task_ids.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: filterData
                    })
                    .then(response => response.json())
                    .then(data => {
                        selectedIDs = new Set(data.map(String)); // Simpan ID ke dalam Set
                        document.querySelectorAll('.task-checkbox').forEach(chk => {
                            const id = chk.value;
                            chk.checked = selectedIDs.has(id); // Cek checkbox jika ID termasuk
                        });
                        updateBulkDelete(); // Update jumlah & input hidden
                    });

                } else {
                    // Jika dibatalkan, kosongkan pilihan
                    selectedIDs.clear();
                    document.querySelectorAll('.task-checkbox').forEach(chk => {
                        chk.checked = false;
                    });
                    updateBulkDelete(); // Update jumlah & input hidden
                }
            });

            // Fungsi untuk memperbarui status tombol dan jumlah terpilih
            function updateBulkDelete() {
                const checkboxes = document.querySelectorAll('.task-checkbox');
                checkboxes.forEach(chk => {
                    const id = chk.value;
                    if (chk.checked) {
                        selectedIDs.add(id);
                    } else {
                        selectedIDs.delete(id);
                    }
                });

                const selectedArray = Array.from(selectedIDs);
                deleteBtn.disabled = selectedArray.length === 0; // Nonaktifkan tombol delete jika kosong
                selectedCount.textContent = selectedArray.length; // Tampilkan jumlah
                bulkIdsInput.value = selectedArray.join(','); // Masukkan ke input hidden
            }

            // Saat ada perubahan checkbox individu
            document.addEventListener('change', e => {
                if (e.target.classList.contains('task-checkbox')) {
                    updateBulkDelete();
                }
            });

            // Fungsi untuk menyesuaikan ulang logika checkbox setelah reload data
            window.rebindCheckboxLogic = function () {
                document.querySelectorAll('.task-checkbox').forEach(chk => {
                    chk.checked = isAllSelected && selectedIDs.has(chk.value);
                });
                updateBulkDelete();
            };

            // Panggil rebind setelah data di-load ulang
            fetchData(true).then(() => {
                rebindCheckboxLogic();
            });
        });
        </script>
    </body>
</html>
        