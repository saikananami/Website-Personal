<?php
    include 'koneksi.php'; // Menyertakan file koneksi ke database
    session_start(); // Memulai session PHP

    // Mengecek apakah user sudah login, jika belum maka diarahkan ke halaman login
    if (!isset($_SESSION['data_user'])) {
        header("Location: login.php");
        exit();
    }

    $id_user = $_SESSION['data_user']['id_user']; // Ambil ID user dari session

    // Atur pagination
    $tasks_per_page = 5;

    // Ambil page dari AJAX (jika ada), default 0
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 0;
    $start = $page * $tasks_per_page;
    
    // Bulk delete handler
    if (isset($_POST['bulk_delete_ids']) && !empty($_POST['bulk_delete_ids'])) {
        // Ubah string ID menjadi array
        $ids = explode(',', $_POST['bulk_delete_ids']);

        // Amankan input dengan mengubah ke integer
        $escaped_ids = array_map('intval', $ids);

        // Gabungkan kembali ID-ID menjadi string untuk query
        $id_list = implode(',', $escaped_ids);

        // Optional: ambil nama-nama task untuk ditampilkan di notifikasi
        $getTasks = mysqli_query($koneksi, "SELECT task FROM tasks WHERE id_task IN ($id_list)");
        $task_names = [];
        while ($row = mysqli_fetch_assoc($getTasks)) {
            $task_names[] = $row['task'];
        }

        // Lakukan query hapus
        $delete = mysqli_query($koneksi, "DELETE FROM tasks WHERE id_task IN ($id_list)");

        // Cek hasil penghapusan
        if ($delete) {
            // Simpan pesan sukses di session
            $_SESSION['message'] = count($task_names) . " task(s) successfully deleted.";
            $_SESSION['message_type'] = 'danger';
            $_SESSION['message_title'] = 'Deleted!';
        } else {
            // Simpan pesan error di session
            $_SESSION['message'] = 'Failed to delete selected tasks.';
            $_SESSION['message_type'] = 'warning';
            $_SESSION['message_title'] = 'Error!';
        }

        // Redirect kembali ke halaman utama
        header("Location: index.php");
        exit();
    }

    // Query awal untuk mengambil data task milik user
    $query = "SELECT * FROM tasks WHERE id_user = '$id_user'";
    
    if (
        empty($_POST['filtersearch']) &&
        empty($_POST['priority']) &&
        empty($_POST['status']) &&
        empty($_POST['filterdate']) &&
        empty($_POST['filtername']) &&
        empty($_POST['filtertime'])
    ) {
        // Jika tidak ada filter apapun yang dipilih, tampilkan hanya task yang belum selesai (status = 0)
        $limit_plus_one = $tasks_per_page + 1;
        $filter_query = "WHERE id_user = '$id_user' AND status = '0'";
        
        $query = "
            SELECT * FROM tasks
            $filter_query
            ORDER BY 
                status ASC,
                -- Prioritaskan berdasarkan urutan: High, Medium, Low, lainnya
                CASE priority
                    WHEN 'High' THEN 0
                    WHEN 'Medium' THEN 1
                    WHEN 'Low' THEN 2
                    ELSE 3
                END ASC,
                -- Urutkan task berdasarkan due_date dan jam sekarang
                CASE
                    WHEN due_date = CURDATE() AND time >= CURTIME() THEN 0 -- Hari ini dan masih ada waktu
                    WHEN due_date = CURDATE() AND time < CURTIME() THEN 1 -- Hari ini tapi sudah lewat waktu
                    WHEN due_date > CURDATE() THEN 2 -- Masih di masa depan
                    ELSE 3 -- Sudah lewat
                END ASC,
                time ASC
            LIMIT $start, $limit_plus_one";
    } else {
        // Jika ada filter yang digunakan, susun kondisi WHERE secara dinamis
        $conditions = ["id_user = '$id_user'"];
    
        // Filter berdasarkan keyword pencarian task
        if (!empty($_POST['filtersearch'])) {
            $keyword = trim(mysqli_real_escape_string($koneksi, $_POST['filtersearch']));
            $conditions[] = "task LIKE '%$keyword%'";
        }
    
        // Filter berdasarkan prioritas
        if (!empty($_POST['priority'])) {
            $priority = $_POST['priority'];
            $conditions[] = "priority = '$priority'";
        }
    
        // Filter berdasarkan status (0 = incomplete, 1 = complete, mixed = semua)
        if (isset($_POST['status'])) {
            if ($_POST['status'] === 'mixed') {
                $conditions[] = "(status = '1' OR status = '0')";
            } elseif ($_POST['status'] === '1') {
                $conditions[] = "status = '1'";
            } elseif ($_POST['status'] === '0') {
                $conditions[] = "status = '0'";
            }
        }
    
        // Filter berdasarkan waktu (pagi, siang, sore)
        if (!empty($_POST['filtertime'])) {
            if ($_POST['filtertime'] === 'morning') {
                $conditions[] = "TIME(time) BETWEEN '05:00:00' AND '11:59:59'";
            } elseif ($_POST['filtertime'] === 'afternoon') {
                $conditions[] = "TIME(time) BETWEEN '12:00:00' AND '17:59:59'";
            } elseif ($_POST['filtertime'] === 'evening') {
                $conditions[] = "TIME(time) BETWEEN '18:00:00' AND '23:59:59'";
            }
        }
    
        // Filter berdasarkan tanggal deadline
        if (!empty($_POST['filterdate'])) {
            $filterDate = mysqli_real_escape_string($koneksi, $_POST['filterdate']);
            $conditions[] = "due_date = '$filterDate'";
        }
    
        // Default sorting: status -> due_date -> time
        $orderBy = "ORDER BY status ASC, due_date ASC, time ASC";
    
        // Jika user ingin sorting berdasarkan nama task (asc/desc)
        if (!empty($_POST['filtername'])) {
            $order = $_POST['filtername'] == 'asc' ? 'ASC' : 'DESC';
            $orderBy = "ORDER BY task $order";
        }
    
        // Gabungkan semua kondisi menjadi 1 string WHERE
        $where = implode(' AND ', $conditions);
        $limit_plus_one = $tasks_per_page + 1;
    
        $query = "
            SELECT * FROM tasks
            WHERE $where
            $orderBy
            LIMIT $start, $limit_plus_one";
    }    
    
    $result = mysqli_query($koneksi, $query); // Jalankan query untuk mengambil data task sesuai filter
    $total_rows = mysqli_num_rows($result); // Hitung total hasil dari query, termasuk 1 tambahan untuk cek "load more"

    // Cek apakah masih ada task selanjutnya
    $has_more = false;
    if ($total_rows > $tasks_per_page) {
        $has_more = true; // Jika hasil lebih banyak dari limit per halaman, berarti masih ada halaman berikutnya
    }

    $display_rows = 0; // Hitung task yang akan ditampilkan (maksimal sebanyak $tasks_per_page)
    $no = $start + 1; // Nomor urut task dimulai dari indeks saat ini (penting untuk pagination)

    $html = ''; // Variabel untuk menyimpan HTML hasil loop

    if (mysqli_num_rows($result) > 0) { // Jika ada task yang ditemukan

        while ($data = mysqli_fetch_assoc($result)) { // Loop setiap task
            if ($display_rows >= $tasks_per_page) break; // Hentikan loop jika sudah mencapai batas tampilan

            $today = date('Y-m-d'); // Tanggal hari ini
            $isLate = ($data['status'] == 0 && $data['due_date'] < $today); // Cek apakah task overdue (telat) dan belum selesai

            // Badge untuk status
            $statusBadge = ($data['status'] == 1)
                ? "<span class='badge rounded-pill status-complete'>Completed</span>" // Jika selesai, tampilkan badge "Completed"
                : "<span class='badge rounded-pill status-incomplete' style='font-size: 11px;
                padding: 2px 6px;'>Incomplete" // Jika belum selesai, tampilkan "Incomplete"
                    . ($isLate ? "<span class='badge bg-danger-subtle text-danger ms-1' style='font-size: 11px;
                    padding: 2px 6px;'>Late</span>" : "") // Tambahkan badge "Late" jika overdue
                . "</span>";

            // Badge untuk prioritas
            $priorityBadge = ($data['priority'] == 1)
                ? "<span class='badge level-low' style='font-size: 11px; padding: 2px 6px;'>Low</span>" // Jika prioritas 1, tampilkan "Low"
                : (($data['priority'] == 2)
                    ? "<span class='badge level-med' style='font-size: 11px; padding: 2px 6px;'>Medium</span>" // Jika prioritas 2, tampilkan "Medium"
                    : "<span class='badge level-high' style='font-size: 11px; padding: 2px 6px;'>High</span>"); // Selain itu, anggap "High"


            // Menambahkan baris baru dalam tabel HTML untuk setiap task yang ditemukan
            $html .= "<tr>
            <!-- Checkbox untuk memilih task, dengan value berupa ID task -->
            <td><input type='checkbox' class='task-checkbox' value='{$data['id_task']}'></td>

            <!-- Menampilkan nomor urut task (misalnya 1, 2, 3, dst.) -->
            <td class='d-none d-md-table-cell' style='font-size: 15px;'>{$no}</td>

            <!-- Menampilkan nama task -->
            <td class='task-description' style='font-size: 15px;'>{$data['task']}</td>

            <!-- Menampilkan deskripsi task -->
            <td class='task-description d-none d-md-table-cell' style='font-size: 15px;'>{$data['task_desc']}</td>

            <!-- Menampilkan badge untuk prioritas task -->
            <td style='font-size: 15px;'>{$priorityBadge}</td>

            <!-- Menampilkan tanggal jatuh tempo task -->
            <td class='d-none d-md-table-cell' style='font-size: 15px;'>{$data['due_date']}</td>

            <!-- Menampilkan waktu jatuh tempo task -->
            <td class='d-none d-md-table-cell' style='font-size: 15px;'>{$data['time']}</td>

            <!-- Menampilkan badge untuk status task (Completed/Incompleted) -->
            <td>{$statusBadge}</td>

            <td>
                <!-- Menampilkan tombol untuk modal info lebih lanjut tentang task -->
                <small class='text-muted'>
                    <!-- Modal info tanggal -->
                    <div class='modal fade' id='dateModal{$data['id_task']}' tabindex='-1' aria-labelledby='dateModalLabel' aria-hidden='true'>
                        <div class='modal-dialog'>
                            <div class='modal-content'>
                                <!-- Header modal dengan judul 'More Info' -->
                                <div class='modal-header'>
                                    <h5 class='modal-title' id='dateModalLabel'>More Info</h5>
                                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                </div>
                                <!-- Body modal yang menampilkan informasi detail task -->
                                <div class='modal-body overflow-auto m-text m-info'>
                                    <!-- Menampilkan nama task -->
                                    <p><strong>Task:</strong> {$data['task']}</p>
                                    
                                    <!-- Menampilkan deskripsi task -->
                                    <p><strong>Task Description:</strong> {$data['task_desc']}</p>
                                
                                    <div class='d-block d-md-none'>
                                        <p><strong>Date:</strong> {$data['due_date']}</p>
                                        <p><strong>Time:</strong> {$data['time']}</p>
                                    </div>

                                    <!-- Menampilkan tanggal task ditambahkan -->
                                    <p><strong>Added:</strong> {$data['added_at']}</p>
                                    
                                    <!-- Menampilkan tanggal task terakhir diperbarui -->
                                    <p><strong>Updated:</strong> {$data['updated_at']}</p>";

                                    // Jika task sudah selesai (status = 1), tampilkan informasi kapan task diselesaikan
                                    if ($data['status'] == 1 && !empty($data['id_task'])) {
                                        $html .= "<p><strong>Completed At:</strong> {$data['completed_at']}</p>";
                                    }

                                // Tutup body modal
                                $html .= "</div>
                        </div>
                    </div>
                </div>
            </small>

            <!-- Dropdown menu dengan berbagai aksi terkait task -->
            <div class='dropdown'>
                <button class='btn' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                    <!-- Tombol dengan ikon tiga titik vertikal untuk membuka dropdown -->
                    <i class='fa-solid fa-ellipsis-vertical'></i>
                </button>
                <!-- Daftar opsi dropdown -->
                <ul class='dropdown-menu'>
                    <!-- Opsi untuk melihat info lebih lanjut tentang task, membuka modal -->
                    <li>
                        <button class='dropdown-item d-flex align-items-center gap-3' data-bs-toggle='modal' data-bs-target='#dateModal{$data['id_task']}'>
                            <i class='fa-solid fa-info'></i> More Info
                        </button>
                    </li>
                    <!-- Opsi untuk mengedit task, membuka modal edit -->
                    <li>
                        <button class='dropdown-item d-flex align-items-center gap-2' data-bs-toggle='modal' data-bs-target='#updateModal{$data['id_task']}'>
                            <i class='fa-solid fa-pen'></i> Edit
                        </button>
                    </li>";

                    // Jika task belum selesai (status = 0), tampilkan opsi untuk menandai task sebagai selesai
                    if ($data['status'] == 0) {
                        $html .= "<li>
                            <!-- Opsi untuk menyelesaikan task, dengan link ke halaman task_complete.php -->
                            <a href='task_complete.php?id_task={$data['id_task']}' class='dropdown-item d-flex align-items-center gap-2'>
                                <i class='fa-solid fa-check'></i> Complete
                            </a>
                        </li>";
                    }
                
                // Tutup dropdown menu
                $html .= "</ul>
            </div>"; // Akhiri kolom aksi dan tutup baris tabel


            // Tutup tag div modal untuk info task sebelumnya
            $html .= "</div>
            </div>
            </div>
            </div>

            <!-- Modal Update Task yang unik untuk setiap task -->
            <div class='modal fade' id='updateModal{$data['id_task']}' tabindex='-1' aria-labelledby='updateModalLabel{$data['id_task']}' aria-hidden='true'>
            <div class='modal-dialog' style='max-width: 300px; margin: auto;'>
            
            <!-- Form untuk mengupdate task -->
            <form method='post' action='task_update.php?id_task={$data['id_task']}'>
                <div class='modal-content'>
                    <div class='modal-header py-1'>
                        <!-- Judul modal dengan nama task yang sedang diupdate -->
                        <h5 class='modal-title' id='updateModalLabel{$data['id_task']}'>Update Task</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Tutup'></button>
                    </div>
                    <!-- Body modal untuk mengedit task -->
                    <div class='modal-body py-1 m-text'>
                        <!-- Input field untuk mengedit nama task -->
                        <div class='mb-3'>
                            <label for='task{$data['id_task']}' class='form-label'>Task</label>
                            <input type='text' class='form-control form-control-sm mb-2' id='task{$data['id_task']}' name='task' value='".htmlspecialchars($data['task'], ENT_QUOTES)."' required>
                        </div>
                        
                        <!-- Input field untuk mengedit deskripsi task -->
                        <div class='mb-3'>
                            <label for='task_desc{$data['id_task']}' class='form-label'>Task Description</label>
                            <input type='text' class='form-control form-control-sm mb-2' id='task_desc{$data['id_task']}' name='task_desc' value='".htmlspecialchars($data['task_desc'], ENT_QUOTES)."' required>
                        </div>
                        
                        <!-- Dropdown untuk memilih prioritas task -->
                        <div class='mb-3'>
                            <label for='priority{$data['id_task']}' class='form-label'>Priority</label>
                            <select class='form-control form-control-sm mb-2' id='priority{$data['id_task']}' name='priority' required>
                                <!-- Prioritas Low (1) -->
                                <option value='1' ".($data['priority'] == 1 ? 'selected' : '').">Low</option>
                                <!-- Prioritas Medium (2) -->
                                <option value='2' ".($data['priority'] == 2 ? 'selected' : '').">Medium</option>
                                <!-- Prioritas High (3) -->
                                <option value='3' ".($data['priority'] == 3 ? 'selected' : '').">High</option>
                            </select>
                        </div>
                        
                        <!-- Input field untuk mengedit tanggal jatuh tempo task -->
                        <div class='mb-3'>
                            <label for='due_date{$data['id_task']}' class='form-label'>Due Date</label>
                            <input type='date' class='form-control form-control-sm mb-2' id='due_date{$data['id_task']}' name='due_date' value='{$data['due_date']}' required>
                        </div>
                        
                        <!-- Input field untuk mengedit waktu task -->
                        <div class='mb-3'>
                            <label for='time{$data['id_task']}' class='form-label'>Time</label>
                            <input type='time' class='form-control form-control-sm mb-2' id='time{$data['id_task']}' name='time' value='{$data['time']}' required>
                        </div>
                        
                        <!-- Dropdown untuk memilih status task (Completed/Incompleted) -->
                        <div class='mb-3'>
                            <label for='status{$data['id_task']}' class='form-label'>Status</label>
                            <select class='form-control form-control-sm mb-2' id='status{$data['id_task']}' name='status' required>
                                <!-- Status Completed hanya bisa dipilih jika task sudah selesai -->
                                <option value='Completed' ".($data['status'] == 1 ? 'selected disabled' : 'disabled').">Completed</option>
                                <!-- Status Incomplete -->
                                <option value='Incomplete' ".($data['status'] == 0 ? 'selected' : '').">Incomplete</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Footer modal dengan tombol Update dan Back -->
                    <div class='modal-footer'>
                        <button type='submit' name='update' class='btn btn-primary btn-sm'>Update</button>
                    </div>
                </div>
            </form>
            </div>
            </div>
            </td>
            </tr>";

    // Menambah nomor urut untuk task berikutnya
    $no++;
    // Menambah jumlah baris yang ditampilkan
    $display_rows++;
    }

    // Jika jumlah task yang ditarik kurang dari batas per_page, artinya sudah tidak ada task lagi
    if (!$has_more) {
    // Menampilkan pesan 'No more tasks to show' di tabel jika tidak ada lagi task
    $html .= "<tr class='no-more-tasks'><td colspan='9' class='text-center text-muted' style='font-size: 14px;'>No more tasks to show</td></tr>";
    }

} elseif ($page === 0) {
// Jika pada halaman pertama dan tidak ada task, tampilkan pesan 'No tasks found'
$html .= "<tr><td colspan='9' class='text-center'>No tasks found</td></tr>";
}

// Menampilkan hasil akhir HTML
echo $html;


?>
