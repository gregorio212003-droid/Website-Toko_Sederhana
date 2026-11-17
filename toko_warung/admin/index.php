<?php

require_once "../config/database.php";
require_once "../includes/admin-auth.php";

/* =====================================================
   NAMA ADMIN
===================================================== */

$nama_admin = $_SESSION['admin_name'] ?? 'Administrator';


/* =====================================================
   HITUNG JUMLAH PRODUK
===================================================== */

$sql_produk = "SELECT COUNT(*) AS total FROM products";

$result_produk = mysqli_query($conn, $sql_produk);

if (!$result_produk) {
    die("Query produk gagal: " . mysqli_error($conn));
}

$data_produk = mysqli_fetch_assoc($result_produk);

$total_produk = (int) $data_produk['total'];


/* =====================================================
   HITUNG JUMLAH KATEGORI
===================================================== */

$sql_kategori = "SELECT COUNT(*) AS total FROM categories";

$result_kategori = mysqli_query($conn, $sql_kategori);

if (!$result_kategori) {
    die("Query kategori gagal: " . mysqli_error($conn));
}

$data_kategori = mysqli_fetch_assoc($result_kategori);

$total_kategori = (int) $data_kategori['total'];


/* =====================================================
   HITUNG JUMLAH PESANAN
===================================================== */

$sql_pesanan = "SELECT COUNT(*) AS total FROM orders";

$result_pesanan = mysqli_query($conn, $sql_pesanan);

if (!$result_pesanan) {
    die("Query pesanan gagal: " . mysqli_error($conn));
}

$data_pesanan = mysqli_fetch_assoc($result_pesanan);

$total_pesanan = (int) $data_pesanan['total'];


/* =====================================================
   HITUNG TOTAL PENJUALAN
===================================================== */

$sql_penjualan = "
    SELECT COALESCE(SUM(total), 0) AS total
    FROM orders
";

$result_penjualan = mysqli_query($conn, $sql_penjualan);

if (!$result_penjualan) {
    die("Query penjualan gagal: " . mysqli_error($conn));
}

$data_penjualan = mysqli_fetch_assoc($result_penjualan);

$total_penjualan = (float) $data_penjualan['total'];

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Admin - Toko Warung</title>


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


        .logout {
            color: white;

            text-decoration: none;

            background: #dc3545;

            padding: 9px 15px;

            border-radius: 6px;

            transition: 0.2s;
        }


        .logout:hover {
            background: #bb2d3b;
        }


        /* =====================================================
           CONTAINER
        ===================================================== */

        .container {
            max-width: 1100px;

            margin: 40px auto;

            padding: 20px;
        }


        /* =====================================================
           WELCOME
        ===================================================== */

        .welcome {
            margin-bottom: 30px;
        }


        .welcome h1 {
            color: #333;

            margin-bottom: 8px;

            font-size: 30px;
        }


        .welcome p {
            color: #666;

            font-size: 16px;
        }


        /* =====================================================
           STATISTICS
        ===================================================== */

        .stats {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 20px;
        }


        /* =====================================================
           CARD
        ===================================================== */

        .card {

            background: white;

            padding: 25px;

            border-radius: 10px;

            box-shadow:
                0 3px 10px
                rgba(0, 0, 0, 0.08);

            transition: 0.2s;
        }


        .card:hover {

            transform: translateY(-2px);

            box-shadow:
                0 5px 15px
                rgba(0, 0, 0, 0.12);
        }


        .card h3 {

            color: #666;

            font-size: 16px;

            margin-bottom: 12px;
        }


        .number {

            font-size: 32px;

            font-weight: bold;

            color: #198754;
        }


        /* =====================================================
           MENU
        ===================================================== */

        .menu {

            margin-top: 30px;

            display: flex;

            gap: 15px;

            flex-wrap: wrap;
        }


        .menu a {

            text-decoration: none;

            background: #198754;

            color: white;

            padding: 12px 20px;

            border-radius: 6px;

            transition: 0.2s;
        }


        .menu a:hover {

            background: #157347;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 600px) {

            .navbar {

                padding: 15px;

            }


            .navbar h2 {

                font-size: 18px;

            }


            .container {

                margin: 20px auto;

                padding: 15px;

            }


            .welcome h1 {

                font-size: 25px;

            }


            .stats {

                grid-template-columns: 1fr;

            }


            .menu {

                flex-direction: column;

            }


            .menu a {

                text-align: center;

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


        <a
            href="logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>



    <!-- =====================================================
         CONTENT
    ===================================================== -->

    <div class="container">


        <!-- WELCOME -->

        <div class="welcome">

            <h1>
                Dashboard Admin
            </h1>


            <p>

                Selamat datang,

                <strong>
                    <?= htmlspecialchars($nama_admin); ?>
                </strong>

            </p>

        </div>



        <!-- =================================================
             STATISTIK
        ================================================= -->

        <div class="stats">


            <!-- TOTAL PRODUK -->

            <div class="card">

                <h3>
                    📦 Total Produk
                </h3>


                <div class="number">

                    <?= $total_produk; ?>

                </div>

            </div>



            <!-- TOTAL KATEGORI -->

            <div class="card">

                <h3>
                    🗂️ Total Kategori
                </h3>


                <div class="number">

                    <?= $total_kategori; ?>

                </div>

            </div>



            <!-- TOTAL PESANAN -->

            <div class="card">

                <h3>
                    🛒 Total Pesanan
                </h3>


                <div class="number">

                    <?= $total_pesanan; ?>

                </div>

            </div>



            <!-- TOTAL PENJUALAN -->

            <div class="card">

                <h3>
                    💰 Total Penjualan
                </h3>


                <div class="number">

                    Rp <?= number_format(
                        $total_penjualan,
                        0,
                        ',',
                        '.'
                    ); ?>

                </div>

            </div>


        </div>



        <!-- =================================================
             MENU ADMIN
        ================================================= -->

        <div class="menu">


            <!-- PRODUK -->

            <a href="produk.php">

                📦 Kelola Produk

            </a>



            <!-- KATEGORI -->

            <a href="kategori.php">

                🗂️ Kelola Kategori

            </a>


        </div>


    </div>


</body>

</html>