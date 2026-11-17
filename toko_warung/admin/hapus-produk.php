<?php

require_once "../config/database.php";
require_once "../includes/admin-auth.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die("ID produk tidak valid.");
}

$sql = "DELETE FROM products WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Query gagal: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    header("Location: produk.php");
    exit;

} else {

    $error = mysqli_stmt_error($stmt);

    mysqli_stmt_close($stmt);

    die("Produk gagal dihapus: " . $error);
}