<?php 
session_start();
if (!isset($_SESSION['data_user'])) {
    header("Location: login.php");
    exit();
}

$id_user = $_SESSION['data_user']['id_user'];
include 'koneksi.php'; // Pastikan koneksi database benar

// Proses bulk/individual delete
if (isset($_POST['bulk_delete_ids']) && !empty($_POST['bulk_delete_ids'])) {
    $task_ids = $_POST['bulk_delete_ids']; // Mengambil daftar ID dari form

    // Mengubah ID yang dipisahkan koma menjadi array
    $task_ids_array = explode(',', $task_ids);

    // Melakukan query delete untuk semua task yang dipilih
    $task_ids_placeholder = implode(',', array_fill(0, count($task_ids_array), '?'));
    $stmt = $koneksi->prepare("DELETE FROM tasks WHERE id_task IN ($task_ids_placeholder)");

    // Menyiapkan parameter untuk bind_param
    $types = str_repeat('i', count($task_ids_array)); // Tipe integer untuk setiap ID
    $stmt->bind_param($types, ...$task_ids_array); 

    // Eksekusi query
    if ($stmt->execute()) {
        $_SESSION['message'] = 'Task(s) successfully deleted.';
        $_SESSION['message_type'] = 'danger';
        $_SESSION['message_title'] = 'Deleted!';
    } else {
        $_SESSION['message'] = 'Error deleting task(s).';
        $_SESSION['message_type'] = 'warning';
        $_SESSION['message_title'] = 'Error!';
    }

    $stmt->close();
    header("Location: index.php");
    exit;
}
?>
