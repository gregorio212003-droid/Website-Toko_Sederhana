<?php

require_once "../config/database.php";
require_once "../includes/admin-auth.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die("ID kategori tidak valid.");
}

/* Cek apakah kategori masih digunakan oleh produk */
$sql = "
    SELECT COUNT(*) AS total
    FROM products
    WHERE category_id = ?
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Query gagal: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

$total_produk = (int) $data['total'];

/* Jika masih digunakan produk */
if ($total_produk > 0) {
    die(
        "Kategori tidak bisa dihapus karena masih digunakan oleh "
        . $total_produk
        . " produk. Silakan pindahkan atau hapus produk tersebut terlebih dahulu."
    );
}

/* Hapus kategori */
$sql = "DELETE FROM categories WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Query gagal: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);

    header("Location: kategori.php");
    exit;
} else {
    $error = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);

    die("Kategori gagal dihapus: " . $error);
}