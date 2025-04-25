<?php
include 'koneksi.php';
session_start();

// Pastikan pengguna sudah login
if (!isset($_SESSION['data_user'])) {
    header("Location: login.php");
    exit();
}

// Pastikan ada parameter id_task yang dikirim melalui URL (GET)
if (isset($_GET['id_task'])) {
    $id_task = (int)$_GET['id_task'];  // Menggunakan $_GET karena id_task dikirimkan melalui URL

    // Ambil status sekarang
    $check = mysqli_query($koneksi, "SELECT task, status, completed_at, added_at, updated_at FROM tasks WHERE id_task = $id_task");
    $row = mysqli_fetch_assoc($check);

    if ($row) {
        $current_status = $row['status'];
        $task_name = $row['task'];

        if ($current_status == 0) {
            // Tandai sebagai completed (waktu completed_at otomatis diset oleh MySQL)
            // Menambahkan added_at dan updated_at untuk tidak terpengaruh perubahan
            $query = mysqli_query($koneksi, "UPDATE tasks 
                                              SET status = 1, 
                                                  completed_at = CURRENT_TIMESTAMP,
                                                  updated_at = updated_at  -- Memastikan updated_at tidak terubah
                                              WHERE id_task = $id_task");

            $_SESSION['message'] = 'Task "' . htmlspecialchars($task_name) . '" successfully marked as completed.';
            $_SESSION['message_type'] = 'success';
            $_SESSION['message_title'] = 'Completed!';
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['message'] = 'Failed to complete the task.';
            $_SESSION['message_type'] = 'warning';
            $_SESSION['message_title'] = 'Oops!';
            header("Location: index.php");
            exit();
        }
        } }
?>
