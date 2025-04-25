<?php
// Tambah task
$id_user = $_SESSION['data_user']['id_user']; // Ambil id_user dari session yang sudah login
    
if(isset($_POST['add_task'])){ // Cek apakah form 'add_task' sudah disubmit
    // Mengambil data dari form
    $task = $_POST['task'];
    $task_desc = $_POST['task_desc'];
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'];
    $time = $_POST['time'];
    
    // Query untuk memasukkan task ke dalam database
    $query = mysqli_query($koneksi, "INSERT INTO tasks (task, task_desc, priority, due_date, time, status, id_user) VALUES 
    ('$task', '$task_desc', '$priority', '$due_date', '$time', '0', '$id_user')");

    // Menyimpan nama task untuk ditampilkan dalam pesan sukses
    $task_name = $_POST['task']; // atau nama field task kamu
    // Menyimpan pesan sukses ke session
    $_SESSION['message'] = 'Task "' . htmlspecialchars($task_name) . '" successfully added!';
    $_SESSION['message_type'] = 'success';
    $_SESSION['message_title'] = 'Success!';
    // Redirect kembali ke halaman index.php
    header("Location: index.php");
    exit;
}

?>

<div class="container mt-3"> 
    <!-- Tombol untuk menambah task -->
    <button id="toggleForm" class="btn btn-primary mb-3 w-100 text-center">
        <i class="fas fa-plus"></i> Add Task
    </button>
    
    <!-- Form untuk menambahkan task, disembunyikan secara default -->
    <div id="taskForm" class="border rounded bg-white p-3 shadow-sm" style="display: none;">
        <form method="POST" class="border rounded bg-light p-2">
            
            <!-- Input untuk judul task -->
            <label class="form-label">Task Title</label>
            <input type="text" name="task" class="form-control" placeholder="Add New Task" 
            autocomplete="off" autofocus required>

            <!-- Input untuk deskripsi task -->
            <label class="form-label">Task Description</label>
            <input type="text" name="task_desc" class="form-control" placeholder="Add Description" 
            autocomplete="off" autofocus required>

            <!-- Dropdown untuk memilih prioritas task -->
            <label class="form-label">Priority</label>
            <select name="priority" class="form-control" required>
                <option value="">Select Priority</option>
                <option value="3">High</option>
                <option value="2">Medium</option>
                <option value="1">Low</option>
            </select>

            <!-- Input untuk memilih tanggal task -->
            <label class="form-label">Date</label>
            <input type="date" name="due_date" class="form-control" value="<?php echo date('Y-m-d')?>" required>
            
            <!-- Input untuk memilih waktu task -->
            <div>
                <label for="time" class="block text-gray-700">Time</label>
                <input type="time" id="time" class="form-control" name="time" required>
            </div>
            
            <!-- Tombol submit untuk menambahkan task -->
            <button type="submit" class="btn btn-primary w-100 mt-2" name="add_task">Add</button>
        </form>
    </div>
    <hr>
    <div class="d-flex align-items-center mb-3 gap-1">
        <!-- Tombol Refresh di kiri -->
        <a href="index.php" id="refreshBtn" class="btn btn-primary">
            <i class="fa-solid fa-arrows-rotate"></i>
        </a>

        <!-- Tombol untuk memilih semua task -->
        <button id="selectAllBtn" class="btn btn-secondary">
            <i class="fa fa-check-square"></i>
        </button>

        <!-- Tombol untuk menghapus task secara massal -->
        <button id="bulkDeleteBtn" class="btn delete-btn" data-bs-toggle="modal" data-bs-target="#bulkDeleteModal" disabled>
            <i class="fa fa-trash"></i>
        </button>
        
        <!-- Spacer otomatis agar elemen berikutnya di sebelah kanan -->
        <div class="flex-grow-1"></div>
        
        <!-- Form pencarian task -->
        <form id="searchForm" method="POST" class="d-flex align-items-center gap-1">
            <div class="flex-grow-1"></div>
            <input type="text" id="filtersearch" class="form-control w-50" placeholder="Search task..." required/>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    <div class="tabel-container" style="width: 100%; overflow-x: hidden;"> 
    <!-- Container untuk tabel dengan scrollbar horizontal agar bisa digulir pada layar kecil -->

    <div class="table-responsive-sm">
        <!-- Tabel yang responsif untuk tampilan layar kecil -->

        <table class="table table-striped text-center table-sm w-100" id="task-list">
            <!-- Tabel utama untuk daftar tugas -->

            <thead class="t-head">
                <!-- Bagian header tabel -->

                <tr>
                    <!-- Baris header tabel -->

                    <th>
                        <label class="l-name"></label> 
                        <!-- Sel header kosong, mungkin untuk penataan atau pemisahan kolom -->
                    </th>

                    <th class="d-none d-md-table-cell">
                        <label class="l-name" style="font-size: 13px;">No</label>
                        <!-- Kolom untuk nomor tugas -->
                    </th>

                    <th>
                        <!-- Kolom untuk tugas dengan opsi filter -->
                        <label class="toggle-filter l-name" 
                               style="font-size: 13px;" onmouseover="this.style.textDecoration='underline'" 
                               onmouseout="this.style.textDecoration='none'" 
                               for="filtername">Task <i class="fa-solid fa-angle-down arrow"></i></label>
                        <!-- Label untuk filter tugas dengan efek hover -->
                        <select class="form-select filter-control" id="filtername" 
                                style="display:none;">
                            <!-- Dropdown untuk pengurutan tugas (A-Z, Z-A, atau campuran) -->
                            <option value="">Mixed</option>
                            <option value="asc">A-Z</option>
                            <option value="desc">Z-A</option>
                        </select>
                    </th>

                    <th class="d-none d-md-table-cell">
                        <label class="l-name" style="font-size: 13px;">Description</label>
                        <!-- Kolom untuk deskripsi tugas -->
                    </th>

                    <th>
                        <!-- Kolom untuk prioritas dengan opsi filter -->
                        <label class="toggle-filter l-name" 
                               style="font-size: 13px;" onmouseover="this.style.textDecoration='underline'" 
                               onmouseout="this.style.textDecoration='none'" 
                               for="filterpriority">Priority <i class="fa-solid fa-angle-down arrow"></i></label>
                        <!-- Label untuk filter prioritas dengan efek hover -->
                        <select class="form-select filter-control" id="filterpriority" 
                                style="display:none;">
                            <!-- Dropdown untuk filter prioritas (Tinggi, Sedang, Rendah) -->
                            <option value="">Mixed</option>
                            <option value="3">High</option>
                            <option value="2">Medium</option>
                            <option value="1">Low</option>
                        </select>
                    </th>

                    <th class="d-none d-md-table-cell">
                        <!-- Kolom untuk tanggal dengan opsi filter -->
                        <label class="toggle-filter l-name" 
                               style="font-size: 13px;" onmouseover="this.style.textDecoration='underline'" 
                               onmouseout="this.style.textDecoration='none'" 
                               for="filterdate">Date <i class="fa-solid fa-angle-down arrow"></i></label>
                        <!-- Label untuk filter tanggal dengan efek hover -->
                        <input type="date" class="form-control filter-control" id="filterdate" 
                               style="display:none;">
                        <!-- Input untuk memilih tanggal -->
                    </th>

                    <th class="d-none d-md-table-cell">
                        <!-- Kolom untuk waktu dengan opsi filter -->
                        <label class="toggle-filter l-name" 
                               style="font-size: 13px;" onmouseover="this.style.textDecoration='underline'" 
                               onmouseout="this.style.textDecoration='none'" 
                               for="filtertime">Time <i class="fa-solid fa-angle-down arrow"></i></label>
                        <!-- Label untuk filter waktu dengan efek hover -->
                        <select class="form-select filter-control" id="filtertime" 
                                style="display:none;">
                            <!-- Dropdown untuk filter waktu (Pagi, Siang, Sore) -->
                            <option value="">Mixed</option>
                            <option value="morning">Morning (00:00 - 11:59)</option>
                            <option value="afternoon">Afternoon (12:00 - 17:59)</option>
                            <option value="evening">Evening (18:00 - 23:59)</option>
                        </select>
                    </th>

                    <th>
                        <!-- Kolom untuk status dengan opsi filter -->
                        <label class="toggle-filter l-name" 
                               style="font-size: 13px;" onmouseover="this.style.textDecoration='underline'" 
                               onmouseout="this.style.textDecoration='none'" 
                               for="filterstatus">Status <i class="fa-solid fa-angle-down arrow"></i></label>
                        <!-- Label untuk filter status dengan efek hover -->
                        <select class="form-select filter-control" id="filterstatus" 
                                style="display:none;">
                            <!-- Dropdown untuk filter status tugas (Belum Selesai, Selesai, Campuran) -->
                            <option value="0">Incomplete</option>
                            <option value="1">Completed</option>
                            <option value="mixed">Mixed</option>
                        </select>
                    </th>

                    <th>
                        <label class="l-name" style="font-size: 13px;">Action</label>
                        <!-- Kolom untuk aksi (misalnya edit atau hapus) -->
                    </th>
                </tr>
            </thead>
            <tbody>
                <!-- Modal Konfirmasi Bulk Delete -->
                <div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-labelledby="bulkDeleteModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <!-- Form untuk mengirim request hapus task dalam jumlah banyak -->
                        <form action="task_delete.php" method="POST" id="bulkDeleteForm">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <!-- Judul modal -->
                                    <h5 class="modal-title" id="bulkDeleteModalLabel">Confirm Delete</h5>
                                    <!-- Tombol untuk menutup modal -->
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body m-text">
                                    <!-- Pesan konfirmasi yang menampilkan jumlah task yang dipilih -->
                                    Are you sure you want to delete <span id="selectedCount">0</span> task(s)?
                                </div>
                                <div class="modal-footer">
                                    <!-- Tombol batal -->
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <!-- Input hidden untuk menyimpan ID-ID task yang akan dihapus -->
                                    <input type="hidden" name="bulk_delete_ids" id="bulk_ids">
                                    <!-- Tombol submit untuk konfirmasi hapus -->
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </tbody>
        </table>
    </div>
       <!-- Tombol untuk memuat lebih banyak data -->
       <div class="text-center my-4">
            <button id="loadMoreBtn" class="btn btn-primary w-100 mt-2">
                <i class="fa-solid fa-arrow-down me-2"></i>Load More
            </button>

        <!-- Indikator loading yang akan ditampilkan ketika data sedang dimuat -->
            <div id="loading" class="loading-spinner" style="display:none;">
                <div class="spinner"></div>
            </div>
        </div>
</div>
