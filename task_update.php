<?php 
include 'koneksi.php';
session_start();
    if (!isset($_SESSION['data_user'])) {
        header("Location: login.php");
        exit();
    }

    
$id_user = $_SESSION['data_user']['id_user'];
$id_task = $_GET['id_task'];
$query = mysqli_query($koneksi, "SELECT * FROM tasks WHERE id_task = $id_task");
$data = mysqli_fetch_assoc($query);

if(isset($_POST['update'])){
    $task = $_POST['task'];
    $task_desc = $_POST['task_desc'];
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'];
    $time = $_POST['time'];
    $status = $_POST['status']; // Ambil nilai status dari form

    $query = mysqli_query($koneksi, "UPDATE tasks SET task = '$task', task_desc = '$task_desc', priority = '$priority', due_date = '$due_date', time = '$time', status = '$status' 
    WHERE id_task = '$id_task'");

    $task_name = $_POST['task'];
    $_SESSION['message'] = 'Task "' . htmlspecialchars($task_name) . '" successfully updated!';
    $_SESSION['message_type'] = 'info';
    $_SESSION['message_title'] = 'Updated!';
    header("Location: index.php");
    exit;


}
?>

