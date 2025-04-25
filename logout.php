<?php
// Mulai session
session_start();

// Hapus semua data session
session_unset();

// Hancurkan session
session_destroy();

// Redirect ke halaman login dengan pesan logout sukses
header("Location: login.php?pesan=logout_success");
exit(); // Hentikan eksekusi script setelah redirect
?>
