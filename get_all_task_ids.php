<?php 
    session_start();
    include 'koneksi.php';
    $id_user = $_SESSION['data_user']['id_user'];

    // Kondisi dasar untuk query
    $conditions = ["id_user = '$id_user'"];

    // Ambil semua filter dari POST
    $filtersearch = $_POST['filtersearch'] ?? '';
    $filterstatus = $_POST['filterstatus'] ?? '';
    $status = $_POST['status'] ?? '';
    $priority = $_POST['priority'] ?? '';
    $filtertime = $_POST['filtertime'] ?? '';

    // Filter berdasarkan status
    if ($status !== '') {
        if ($status === 'mixed') {
            $conditions[] = "(status = '1' OR status = '0')";
        } elseif ($status === '1') {
            $conditions[] = "status = '1'";
        } elseif ($status === '0') {
            $conditions[] = "status = '0'";
        }
    }

    // Filter berdasarkan priority
    if ($priority !== '') {
        $conditions[] = "priority = '$priority'";
    }

    // Filter berdasarkan pencarian
    if ($filtersearch !== '') {
        $keyword = trim(mysqli_real_escape_string($koneksi, $filtersearch));
        $conditions[] = "task LIKE '%$keyword%'";
    }

    // Filter berdasarkan waktu
    if ($filtertime === 'morning') {
        $conditions[] = "TIME(time) BETWEEN '05:00:00' AND '11:59:59'";
    } elseif ($filtertime === 'afternoon') {
        $conditions[] = "TIME(time) BETWEEN '12:00:00' AND '17:59:59'";
    } elseif ($filtertime === 'evening') {
        $conditions[] = "TIME(time) BETWEEN '18:00:00' AND '23:59:59'";
    }


    // Gabungkan kondisi filter yang sudah diterapkan
    $where = implode(' AND ', $conditions);

    // Query untuk mengambil ID task berdasarkan kondisi filter
    $query = "SELECT id_task FROM tasks WHERE $where";
    $result = mysqli_query($koneksi, $query);

    // Ambil semua ID task yang sesuai dengan filter
    $ids = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $ids[] = $row['id_task'];
    }

    // Kembalikan ID task dalam format JSON
    echo json_encode($ids);
?>
