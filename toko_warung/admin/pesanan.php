<?php

require_once "../config/database.php";
require_once "../includes/admin-auth.php";


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
    ORDER BY id DESC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die(
        "Query pesanan gagal: "
        . mysqli_error($conn)
    );
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
    Pesanan - Admin Toko Warung
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


.navbar-menu {

    display: flex;

    gap: 10px;

    align-items: center;

}


.navbar-menu a {

    color: white;

    text-decoration: none;

    padding: 10px 16px;

    border-radius: 6px;

    background: rgba(255,255,255,0.12);

}


.navbar-menu a:hover {

    background: rgba(255,255,255,0.22);

}


.navbar-menu .logout {

    background: #dc3545;

}


.navbar-menu .logout:hover {

    background: #bb2d3b;

}


/* =====================================================
   CONTAINER
===================================================== */

.container {

    width: 90%;

    max-width: 1200px;

    margin: 50px auto;

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

    font-size: 32px;

    color: #333;

}


.back-button {

    background: #6c757d;

    color: white;

    text-decoration: none;

    padding: 11px 18px;

    border-radius: 6px;

}


.back-button:hover {

    background: #5c636a;

}


/* =====================================================
   CARD
===================================================== */

.card {

    background: white;

    border-radius: 10px;

    padding: 20px;

    box-shadow:
        0 3px 10px
        rgba(0,0,0,0.08);

    overflow-x: auto;

}


/* =====================================================
   TABLE
===================================================== */

table {

    width: 100%;

    border-collapse: collapse;

    min-width: 1050px;

}


thead {

    background: #198754;

    color: white;

}


th {

    padding: 14px 12px;

    text-align: left;

    font-size: 15px;

}


td {

    padding: 14px 12px;

    border-bottom: 1px solid #eee;

    vertical-align: middle;

}


tbody tr:hover {

    background: #f8f9fa;

}


/* =====================================================
   TOTAL
===================================================== */

.total {

    font-weight: bold;

    color: #198754;

    white-space: nowrap;

}


/* =====================================================
   STATUS
===================================================== */

.status {

    display: inline-block;

    padding: 7px 12px;

    border-radius: 20px;

    font-size: 13px;

    font-weight: bold;

    white-space: nowrap;

}


.status-menunggu {

    background: #fff3cd;

    color: #664d03;

}


.status-diproses {

    background: #cfe2ff;

    color: #084298;

}


.status-selesai {

    background: #d1e7dd;

    color: #0f5132;

}


.status-dibatalkan {

    background: #f8d7da;

    color: #842029;

}


/* =====================================================
   ACTION BUTTONS
===================================================== */

.actions {

    display: flex;

    gap: 7px;

    flex-wrap: wrap;

}


.btn {

    display: inline-block;

    padding: 8px 12px;

    border-radius: 6px;

    color: white;

    text-decoration: none;

    font-size: 13px;

    border: none;

    cursor: pointer;

}


.btn-detail {

    background: #0d6efd;

}


.btn-detail:hover {

    background: #0b5ed7;

}


.btn-delete {

    background: #dc3545;

}


.btn-delete:hover {

    background: #bb2d3b;

}


/* =====================================================
   EMPTY DATA
===================================================== */

.empty {

    text-align: center;

    padding: 40px;

    color: #777;

    font-size: 16px;

}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 768px) {

    .navbar {

        flex-direction: column;

        gap: 15px;

        align-items: flex-start;

    }


    .navbar-menu {

        flex-wrap: wrap;

    }


    .page-header {

        flex-direction: column;

        align-items: flex-start;

        gap: 15px;

    }


    .page-header h1 {

        font-size: 26px;

    }


    .container {

        width: 95%;

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


    <div class="navbar-menu">


        <a href="index.php">
            Dashboard
        </a>


        <a href="produk.php">
            Produk
        </a>


        <a href="kategori.php">
            Kategori
        </a>


        <a
            href="logout.php"
            class="logout"
        >
            Logout
        </a>


    </div>


</div>



<!-- =====================================================
     CONTENT
===================================================== -->

<div class="container">


    <div class="page-header">


        <h1>
            🛒 Daftar Pesanan
        </h1>


        <a
            href="index.php"
            class="back-button"
        >
            ← Kembali ke Dashboard
        </a>


    </div>



    <div class="card">


        <?php if (mysqli_num_rows($result) > 0): ?>


            <table>


                <thead>

                    <tr>

                        <th>
                            ID
                        </th>

                        <th>
                            Pelanggan
                        </th>

                        <th>
                            No. HP
                        </th>

                        <th>
                            Alamat
                        </th>

                        <th>
                            Catatan
                        </th>

                        <th>
                            Total
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Tanggal
                        </th>

                        <th>
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody>


                    <?php while ($order = mysqli_fetch_assoc($result)): ?>


                        <?php

                        $status = strtolower(
                            trim($order['status'])
                        );

                        $status_class = 'status-menunggu';

                        if ($status === 'diproses') {

                            $status_class = 'status-diproses';

                        } elseif ($status === 'selesai') {

                            $status_class = 'status-selesai';

                        } elseif ($status === 'dibatalkan') {

                            $status_class = 'status-dibatalkan';

                        }

                        ?>


                        <tr>


                            <td>

                                <?= (int) $order['id']; ?>

                            </td>


                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                        $order['customer_name']
                                    ); ?>

                                </strong>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $order['customer_phone']
                                ); ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $order['customer_address']
                                ); ?>

                            </td>


                            <td>

                                <?php

                                if (
                                    !empty(
                                        $order['notes']
                                    )
                                ) {

                                    echo htmlspecialchars(
                                        $order['notes']
                                    );

                                } else {

                                    echo '-';

                                }

                                ?>

                            </td>


                            <td class="total">

                                Rp

                                <?= number_format(
                                    $order['total'],
                                    0,
                                    ',',
                                    '.'
                                ); ?>

                            </td>


                            <td>

                                <span
                                    class="status
                                    <?= $status_class; ?>"
                                >

                                    <?= htmlspecialchars(
                                        $order['status']
                                    ); ?>

                                </span>

                            </td>


                            <td>

                                <?= date(
                                    'd-m-Y H:i',
                                    strtotime(
                                        $order['created_at']
                                    )
                                ); ?>

                            </td>


                            <td>


                                <div class="actions">


                                    <!-- DETAIL -->

                                    <a
                                        href="detail-pesanan.php?id=<?= (int) $order['id']; ?>"
                                        class="btn btn-detail"
                                    >

                                        👁️ Detail

                                    </a>



                                    <!-- HAPUS -->

                                    <a
                                        href="hapus-pesanan.php?id=<?= (int) $order['id']; ?>"
                                        class="btn btn-delete"
                                    >

                                        🗑️ Hapus

                                    </a>


                                </div>


                            </td>


                        </tr>


                    <?php endwhile; ?>


                </tbody>


            </table>


        <?php else: ?>


            <div class="empty">

                🛒 Belum ada pesanan.

            </div>


        <?php endif; ?>


    </div>


</div>


</body>

</html>