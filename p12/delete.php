<?php

include 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: index.php'); exit; }

$stmt = mysqli_prepare($conn, "SELECT photo FROM students WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
$photo = $row ? $row['photo'] : null;

$stmt2 = mysqli_prepare($conn, "DELETE FROM students WHERE id = ?");
mysqli_stmt_bind_param($stmt2, "i", $id);
if (mysqli_stmt_execute($stmt2)) {
    if ($photo && file_exists('uploads/'.$photo)) {
        @unlink('uploads/'.$photo);
    }
}

header('Location: index.php');
exit;
?>