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
   PROSES PERUBAHAN STATUS
===================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $status = $_POST['status'] ?? '';

    $status_valid = [
        'Menunggu',
        'Diproses',
        'Selesai',
        'Dibatalkan'
    ];

    if (!in_array($status, $status_valid, true)) {

        die("Status pesanan tidak valid.");

    }


    $sql = "
        UPDATE orders
        SET status = ?
        WHERE id = ?
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("Query gagal: " . mysqli_error($conn));
    }


    mysqli_stmt_bind_param(
        $stmt,
        "si",
        $status,
        $id
    );


    if (!mysqli_stmt_execute($stmt)) {

        $error = mysqli_stmt_error($stmt);

        mysqli_stmt_close($stmt);

        die("Status gagal diperbarui: " . $error);
    }


    mysqli_stmt_close($stmt);


    /* Kembali ke halaman detail */

    header(
        "Location: detail-pesanan.php?id=" . $id
    );

    exit;
}


/* =====================================================
   AMBIL DATA PESANAN
===================================================== */

$sql = "
    SELECT
        id,
        customer_name,
        customer_phone,
        customer_address,
        notes,
        total,
        status,
        created_at
    FROM orders
    WHERE id = ?
    LIMIT 1
";


$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Query gagal: " . mysqli_error($conn));
}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);


mysqli_stmt_execute($stmt);


$result = mysqli_stmt_get_result($stmt);


$order = mysqli_fetch_assoc($result);


mysqli_stmt_close($stmt);


if (!$order) {
    die("Pesanan tidak ditemukan.");
}


/* =====================================================
   AMBIL DETAIL PRODUK
===================================================== */

$sql = "
    SELECT
        id,
        product_id,
        product_name,
        price,
        quantity,
        subtotal
    FROM order_details
    WHERE order_id = ?
    ORDER BY id ASC
";


$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Query detail pesanan gagal: " . mysqli_error($conn));
}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);


mysqli_stmt_execute($stmt);


$details = mysqli_stmt_get_result($stmt);

?>


<!DOCTYPE html>

<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>
Detail Pesanan #<?= (int) $order['id']; ?>
</title>


<style>

/* =====================================================
   RESET
===================================================== */

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}


/* =====================================================
   BODY
===================================================== */

body {

    font-family: Arial, sans-serif;

    background: #f4f6f9;

    color: #333;

}


/* =====================================================
   NAVBAR
===================================================== */

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


.navbar-links {

    display: flex;

    gap: 10px;

}


.navbar-links a {

    color: white;

    text-decoration: none;

    padding: 9px 14px;

    border-radius: 6px;

    background: rgba(255,255,255,0.15);

}


.navbar-links a:hover {

    background: rgba(255,255,255,0.25);

}


.logout {

    background: #dc3545 !important;

}


/* =====================================================
   CONTAINER
===================================================== */

.container {

    width: 92%;

    max-width: 1100px;

    margin: 40px auto;

}


/* =====================================================
   HEADER
===================================================== */

.page-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 25px;

}


.page-header h1 {

    font-size: 30px;

}


.back {

    background: #6c757d;

    color: white;

    text-decoration: none;

    padding: 11px 18px;

    border-radius: 6px;

}


/* =====================================================
   CARD
===================================================== */

.card {

    background: white;

    padding: 30px;

    border-radius: 10px;

    margin-bottom: 25px;

    box-shadow:

        0 3px 10px

        rgba(0,0,0,0.08);

}


.card h2 {

    margin-bottom: 25px;

    font-size: 22px;

}


/* =====================================================
   INFORMASI PESANAN
===================================================== */

.info-grid {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 25px;

}


.info-item {

    margin-bottom: 10px;

}


.info-label {

    display: block;

    color: #777;

    font-size: 14px;

    margin-bottom: 5px;

}


.info-value {

    font-size: 16px;

    font-weight: bold;

}


/* =====================================================
   STATUS
===================================================== */

.status-form {

    margin-top: 25px;

    padding-top: 20px;

    border-top: 1px solid #eee;

}


.status-form label {

    display: block;

    font-weight: bold;

    margin-bottom: 8px;

}


.status-form select {

    width: 250px;

    padding: 11px;

    border: 1px solid #ccc;

    border-radius: 6px;

    font-size: 15px;

}


.status-form button {

    padding: 11px 18px;

    margin-left: 8px;

    border: none;

    border-radius: 6px;

    background: #198754;

    color: white;

    cursor: pointer;

    font-size: 15px;

}


.status-form button:hover {

    background: #157347;

}


/* =====================================================
   TABLE
===================================================== */

.table-wrapper {

    width: 100%;

    overflow-x: auto;

}


table {

    width: 100%;

    border-collapse: collapse;

}


th {

    background: #198754;

    color: white;

    padding: 12px;

    text-align: left;

}


td {

    padding: 12px;

    border-bottom: 1px solid #eee;

}


.total-row td {

    font-weight: bold;

    font-size: 17px;

}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 700px) {

    .navbar {

        flex-direction: column;

        align-items: flex-start;

        gap: 12px;

    }


    .page-header {

        flex-direction: column;

        align-items: flex-start;

        gap: 15px;

    }


    .info-grid {

        grid-template-columns: 1fr;

    }


    .status-form select {

        width: 100%;

        margin-bottom: 10px;

    }


    .status-form button {

        margin-left: 0;

    }

}

</style>

</head>


<body>


<!-- =====================================================
     NAVBAR
===================================================== -->

<div class="navbar">


    <h2>
        🛒 Admin Toko Warung
    </h2>


    <div class="navbar-links">


        <a href="index.php">
            Dashboard
        </a>


        <a href="produk.php">
            Produk
        </a>


        <a href="kategori.php">
            Kategori
        </a>


        <a href="pesanan.php">
            Pesanan
        </a>


        <a href="logout.php" class="logout">
            Logout
        </a>


    </div>


</div>



<!-- =====================================================
     CONTENT
===================================================== -->

<div class="container">


    <!-- HEADER -->

    <div class="page-header">


        <h1>
            🛒 Detail Pesanan #<?= (int) $order['id']; ?>
        </h1>


        <a
            href="pesanan.php"
            class="back"
        >
            ← Kembali ke Pesanan
        </a>


    </div>



    <!-- =================================================
         INFORMASI PESANAN
    ================================================= -->

    <div class="card">


        <h2>
            Informasi Pesanan
        </h2>


        <div class="info-grid">


            <div>


                <div class="info-item">

                    <span class="info-label">
                        Nama Pelanggan
                    </span>

                    <span class="info-value">

                        <?= htmlspecialchars(
                            $order['customer_name']
                        ); ?>

                    </span>

                </div>


                <div class="info-item">

                    <span class="info-label">
                        Nomor Telepon
                    </span>

                    <span class="info-value">

                        <?= htmlspecialchars(
                            $order['customer_phone']
                        ); ?>

                    </span>

                </div>


                <div class="info-item">

                    <span class="info-label">
                        Alamat
                    </span>

                    <span class="info-value">

                        <?= htmlspecialchars(
                            $order['customer_address']
                        ); ?>

                    </span>

                </div>


            </div>



            <div>


                <div class="info-item">

                    <span class="info-label">
                        Status Pesanan
                    </span>

                    <span class="info-value">

                        <?= htmlspecialchars(
                            $order['status']
                        ); ?>

                    </span>

                </div>


                <div class="info-item">

                    <span class="info-label">
                        Tanggal Pesanan
                    </span>

                    <span class="info-value">

                        <?= date(
                            'd-m-Y H:i',
                            strtotime(
                                $order['created_at']
                            )
                        ); ?>

                    </span>

                </div>


                <div class="info-item">

                    <span class="info-label">
                        Catatan
                    </span>

                    <span class="info-value">

                        <?php if (
                            !empty($order['notes'])
                        ): ?>

                            <?= htmlspecialchars(
                                $order['notes']
                            ); ?>

                        <?php else: ?>

                            <span style="color:#999;">
                                Tidak ada catatan
                            </span>

                        <?php endif; ?>

                    </span>

                </div>


            </div>


        </div>



        <!-- =================================================
             FORM STATUS
        ================================================= -->

        <div class="status-form">


            <form method="POST">


                <label for="status">
                    Ubah Status Pesanan
                </label>


                <select
                    name="status"
                    id="status"
                    required
                >


                    <option
                        value="Menunggu"
                        <?= $order['status'] === 'Menunggu'
                            ? 'selected'
                            : ''; ?>
                    >
                        Menunggu
                    </option>


                    <option
                        value="Diproses"
                        <?= $order['status'] === 'Diproses'
                            ? 'selected'
                            : ''; ?>
                    >
                        Diproses
                    </option>


                    <option
                        value="Selesai"
                        <?= $order['status'] === 'Selesai'
                            ? 'selected'
                            : ''; ?>
                    >
                        Selesai
                    </option>


                    <option
                        value="Dibatalkan"
                        <?= $order['status'] === 'Dibatalkan'
                            ? 'selected'
                            : ''; ?>
                    >
                        Dibatalkan
                    </option>


                </select>


                <button type="submit">
                    Simpan Status
                </button>


            </form>


        </div>


    </div>



    <!-- =================================================
         PRODUK PESANAN
    ================================================= -->

    <div class="card">


        <h2>
            Produk yang Dibeli
        </h2>


        <div class="table-wrapper">


            <table>


                <thead>

                    <tr>

                        <th>
                            Produk
                        </th>

                        <th>
                            Harga
                        </th>

                        <th>
                            Jumlah
                        </th>

                        <th>
                            Subtotal
                        </th>

                    </tr>

                </thead>


                <tbody>


                    <?php if (
                        mysqli_num_rows($details) > 0
                    ): ?>


                        <?php while (
                            $detail =
                            mysqli_fetch_assoc($details)
                        ): ?>


                            <tr>


                                <td>

                                    <?= htmlspecialchars(
                                        $detail['product_name']
                                    ); ?>

                                </td>


                                <td>

                                    Rp
                                    <?= number_format(
                                        (float)
                                        $detail['price'],
                                        0,
                                        ',',
                                        '.'
                                    ); ?>

                                </td>


                                <td>

                                    <?= (int)
                                        $detail['quantity']; ?>

                                </td>


                                <td>

                                    Rp
                                    <?= number_format(
                                        (float)
                                        $detail['subtotal'],
                                        0,
                                        ',',
                                        '.'
                                    ); ?>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="4"
                                style="
                                    text-align:center;
                                    color:#777;
                                "
                            >

                                Tidak ada produk
                                dalam pesanan ini.

                            </td>

                        </tr>


                    <?php endif; ?>


                    <!-- TOTAL -->

                    <tr class="total-row">


                        <td colspan="3">

                            Total Pesanan

                        </td>


                        <td>

                            Rp
                            <?= number_format(
                                (float) $order['total'],
                                0,
                                ',',
                                '.'
                            ); ?>

                        </td>


                    </tr>


                </tbody>


            </table>


        </div>


    </div>


</div>


</body>

</html>