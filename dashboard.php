<?php
    // Koneksi ke database
    include "koneksi.php";


    // Ambil ID user dari session
    $id_user = $_SESSION['data_user']['id_user'];

    // Hitung total task milik user
    $total = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) 
    AS total FROM tasks WHERE id_user = '$id_user'"))['total'];

    // Hitung task yang belum selesai
    $incompleted = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) 
    AS total FROM tasks WHERE status = '0' AND id_user = '$id_user'"))['total'];

    // Hitung task yang selesai
    $completed = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) 
    AS total FROM tasks WHERE status = '1' AND id_user = '$id_user'"))['total'];

    // Hitung task yang terlambat
    $late = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) 
    AS total FROM tasks WHERE status = '0' AND due_date < CURDATE() AND id_user = '$id_user'"))['total'];
?>

<div class="container py-4">
    <div class="row g-4">
        <!-- Tugas Terbaru -->
        <div class="mt-4">
            <h5 class="mb-31">Recent Tasks</h5>
            <ul class="list-group shadow-sm rounded-4">
                <?php
                $recentTasks = mysqli_query($koneksi, "SELECT * FROM tasks ORDER BY id_task DESC LIMIT 5");
                while ($task = mysqli_fetch_assoc($recentTasks)) {
                    // Badge untuk prioritas (sesuai style kamu)
                    $priorityBadge = ($task['priority'] == 1)
                        ? "<span class='badge level-low'>Low</span>"
                        : (($task['priority'] == 2)
                            ? "<span class='badge level-med'>Medium</span>"
                            : "<span class='badge level-high'>High</span>");

                    echo "<li class='list-group-item'>
                            <div class='d-flex flex-column'>
                                <strong>{$task['task']}</strong>
                                <div class='mt-1 small text-muted'>
                                    {$task['due_date']} at {$task['time']} 
                                    <span class='ms-2'>{$priorityBadge}</span>
                                </div>
                            </div>
                        </li>";
                }
                ?>
            </ul>
        </div>
        <!-- Kartu: Total Tasks -->
        <div class="col-md-3 mb-3">
            <div class="card text-white shadow rounded-4" style="background-color: #9da099;">
                <div class="card-body text-center py-4">
                    <h5 class="mb-2 fw-semibold">Total Tasks</h5>
                    <h2 class="fw-bold"><?= $total ?></h2>
                </div>
            </div>
        </div>

        <!-- Kartu: Incompleted Tasks -->
        <div class="col-md-3 mb-3">
            <div class="card text-white shadow rounded-4" style="background-color: #ff8091;">
                <div class="card-body text-center py-4">
                    <h5 class="mb-2 fw-semibold">Incompleted</h5>
                    <h2 class="fw-bold"><?= $incompleted ?></h2>
                </div>
            </div>
        </div>

        <!-- Kartu: Completed Tasks -->
        <div class="col-md-3 mb-3">
            <div class="card text-white shadow rounded-4" style="background-color: #84dd87;">
                <div class="card-body text-center py-4">
                    <h5 class="mb-2 fw-semibold">Completed</h5>
                    <h2 class="fw-bold"><?= $completed ?></h2>
                </div>
            </div>
        </div>

        <!-- Kartu: Late Tasks -->
        <div class="col-md-3 mb-3">
            <div class="card text-white shadow rounded-4" style="background-color: #c498a3;">
                <div class="card-body text-center py-4">
                    <h5 class="mb-2 fw-semibold">Late Tasks</h5>
                    <h2 class="fw-bold"><?= $late ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>

