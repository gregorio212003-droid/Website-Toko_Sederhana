<?php

require_once "../config/database.php";
require_once "../includes/admin-auth.php";


/* =====================================================
   AMBIL ID PESANAN
===================================================== */

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die("ID pesanan tidak valid.");
}


/* =====================================================
   CEK APAKAH PESANAN ADA
===================================================== */

$sql = "
    SELECT id, customer_name, status
    FROM orders
    WHERE id = ?
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Query gagal: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$order = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$order) {
    die("Pesanan tidak ditemukan.");
}


/* =====================================================
   PROSES HAPUS
===================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $confirmation = $_POST['confirmation'] ?? '';

    if ($confirmation !== 'HAPUS') {

        die("Konfirmasi penghapusan tidak valid.");

    }


    /* Mulai transaksi */

    mysqli_begin_transaction($conn);


    try {

        /* =================================================
           HAPUS DETAIL PESANAN
        ================================================= */

        $sql = "
            DELETE FROM order_details
            WHERE order_id = ?
        ";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            throw new Exception(
                "Gagal menyiapkan penghapusan detail pesanan."
            );
        }

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $id
        );

        if (!mysqli_stmt_execute($stmt)) {

            throw new Exception(
                "Detail pesanan gagal dihapus: "
                . mysqli_stmt_error($stmt)
            );

        }

        mysqli_stmt_close($stmt);


        /* =================================================
           HAPUS PESANAN UTAMA
        ================================================= */

        $sql = "
            DELETE FROM orders
            WHERE id = ?
        ";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            throw new Exception(
                "Gagal menyiapkan penghapusan pesanan."
            );
        }

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $id
        );

        if (!mysqli_stmt_execute($stmt)) {

            throw new Exception(
                "Pesanan gagal dihapus: "
                . mysqli_stmt_error($stmt)
            );

        }

        mysqli_stmt_close($stmt);


        /* =================================================
           SIMPAN PERUBAHAN
        ================================================= */

        mysqli_commit($conn);


        /* Kembali ke daftar pesanan */

        header("Location: pesanan.php");

        exit;


    } catch (Exception $e) {

        /* Batalkan semua perubahan */

        mysqli_rollback($conn);


        die(
            "Pesanan gagal dihapus.<br><br>"
            . htmlspecialchars($e->getMessage())
        );

    }

}

?>


<!DOCTYPE html>

<html lang="id">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Hapus Pesanan - Admin
</title>


<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}


body {

    font-family: Arial, sans-serif;

    background: #f4f6f9;

    color: #333;

}


.navbar {

    background: #198754;

    color: white;

    padding: 18px 30px;

    display: flex;

    justify-content: space-between;

    align-items: center;

}


.navbar h2 {

    font-size: 22px;

}


.logout {

    color: white;

    text-decoration: none;

    background: #dc3545;

    padding: 9px 15px;

    border-radius: 6px;

}


.container {

    width: 90%;

    max-width: 600px;

    margin: 60px auto;

}


.card {

    background: white;

    padding: 30px;

    border-radius: 10px;

    box-shadow:
        0 3px 10px
        rgba(0, 0, 0, 0.08);

}


h1 {

    margin-bottom: 20px;

    color: #dc3545;

}


.warning {

    background: #fff3cd;

    color: #664d03;

    padding: 15px;

    border-radius: 6px;

    margin-bottom: 20px;

    line-height: 1.6;

}


.info {

    background: #f8f9fa;

    padding: 15px;

    border-radius: 6px;

    margin-bottom: 20px;

    line-height: 1.8;

}


label {

    display: block;

    font-weight: bold;

    margin-bottom: 8px;

}


input {

    width: 100%;

    padding: 12px;

    border: 1px solid #ccc;

    border-radius: 6px;

    font-size: 15px;

    margin-bottom: 20px;

}


.buttons {

    display: flex;

    gap: 10px;

}


button,
a {

    padding: 11px 18px;

    border-radius: 6px;

    border: none;

    text-decoration: none;

    cursor: pointer;

    font-size: 15px;

}


button {

    background: #dc3545;

    color: white;

}


button:hover {

    background: #bb2d3b;

}


.back {

    background: #6c757d;

    color: white;

}


.back:hover {

    background: #5c636a;

}

</style>

</head>


<body>


<div class="navbar">


    <h2>
        🛒 Admin Toko Warung
    </h2>


    <a
        href="logout.php"
        class="logout"
    >
        Logout
    </a>


</div>



<div class="container">


    <div class="card">


        <h1>
            🗑️ Hapus Pesanan
        </h1>


        <div class="warning">

            <strong>
                PERINGATAN!
            </strong>

            <br>

            Pesanan ini akan dihapus
            secara permanen.

            <br>

            Data detail produk yang
            terkait dengan pesanan ini
            juga akan dihapus.

        </div>



        <div class="info">

            <strong>
                ID Pesanan:
            </strong>

            #<?= (int) $order['id']; ?>

            <br>


            <strong>
                Pelanggan:
            </strong>

            <?= htmlspecialchars(
                $order['customer_name']
            ); ?>

            <br>


            <strong>
                Status:
            </strong>

            <?= htmlspecialchars(
                $order['status']
            ); ?>

        </div>



        <form method="POST">


            <label for="confirmation">

                Ketik
                <strong>HAPUS</strong>
                untuk melanjutkan:

            </label>


            <input
                type="text"
                name="confirmation"
                id="confirmation"
                placeholder="Ketik HAPUS"
                autocomplete="off"
                required
            >


            <div class="buttons">


                <button type="submit">

                    🗑️ Hapus Pesanan

                </button>


                <a
                    href="pesanan.php"
                    class="back"
                >

                    Batal

                </a>


            </div>


        </form>


    </div>


</div>


</body>

</html>