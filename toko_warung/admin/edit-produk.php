<?php

require_once "../config/database.php";
require_once "../includes/admin-auth.php";


// =====================================================
// 1. CEK ID PRODUK
// =====================================================

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die("ID produk tidak valid.");
}


// =====================================================
// 2. AMBIL DATA PRODUK
// =====================================================

$sql = "
    SELECT id, category_id, name, description, price, stock
    FROM products
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
$product = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$product) {
    die("Produk tidak ditemukan.");
}


// =====================================================
// 3. PROSES UPDATE
// =====================================================

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $stock = $_POST['stock'] ?? '';
    $category_id = $_POST['category_id'] ?? '';


    // ---------------------------------------------
    // Validasi
    // ---------------------------------------------

    if (
        $name === '' ||
        $price === '' ||
        $stock === '' ||
        $category_id === ''
    ) {

        $error = "Nama, harga, stok, dan kategori wajib diisi.";

    } elseif (!is_numeric($price) || $price < 0) {

        $error = "Harga tidak valid.";

    } elseif (!is_numeric($stock) || $stock < 0) {

        $error = "Stok tidak valid.";

    } else {

        $price = (float) $price;
        $stock = (int) $stock;
        $category_id = (int) $category_id;


        // ---------------------------------------------
        // UPDATE DATABASE
        // ---------------------------------------------

        $sql = "
            UPDATE products
            SET
                category_id = ?,
                name = ?,
                description = ?,
                price = ?,
                stock = ?
            WHERE id = ?
        ";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {

            $error = "Query gagal: " . mysqli_error($conn);

        } else {

            mysqli_stmt_bind_param(
                $stmt,
                "issdii",
                $category_id,
                $name,
                $description,
                $price,
                $stock,
                $id
            );


            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header("Location: produk.php");
                exit;

            } else {

                $error = "Produk gagal diperbarui: "
                       . mysqli_stmt_error($stmt);

                mysqli_stmt_close($stmt);
            }
        }
    }


    // Jika ada error, tampilkan kembali data yang diketik
    $product['name'] = $name;
    $product['description'] = $description;
    $product['price'] = $price;
    $product['stock'] = $stock;
    $product['category_id'] = $category_id;
}


// =====================================================
// 4. AMBIL SEMUA KATEGORI
// =====================================================

$category_query = mysqli_query(
    $conn,
    "SELECT id, name FROM categories ORDER BY name ASC"
);

if (!$category_query) {
    die("Gagal mengambil kategori: " . mysqli_error($conn));
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

    <title>Edit Produk - Admin</title>


    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
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
            max-width: 700px;

            margin: 30px auto;

            padding: 0 20px;
        }


        .card {
            background: white;

            padding: 30px;

            border-radius: 10px;

            box-shadow:
                0 3px 10px rgba(0, 0, 0, 0.08);
        }


        h1 {
            margin-bottom: 25px;

            color: #333;
        }


        .form-group {
            margin-bottom: 20px;
        }


        label {
            display: block;

            margin-bottom: 8px;

            font-weight: bold;
        }


        input,
        textarea,
        select {

            width: 100%;

            padding: 12px;

            border: 1px solid #ccc;

            border-radius: 6px;

            font-size: 15px;
        }


        textarea {

            min-height: 100px;

            resize: vertical;
        }


        .buttons {

            display: flex;

            gap: 10px;

            margin-top: 25px;
        }


        .btn {

            padding: 11px 18px;

            border: none;

            border-radius: 6px;

            text-decoration: none;

            cursor: pointer;

            font-size: 15px;
        }


        .btn-primary {

            background: #198754;

            color: white;
        }


        .btn-secondary {

            background: #6c757d;

            color: white;
        }


        .error {

            background: #f8d7da;

            color: #842029;

            padding: 12px;

            border-radius: 6px;

            margin-bottom: 20px;
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
                Edit Produk
            </h1>


            <?php if ($error !== ''): ?>

                <div class="error">

                    <?= htmlspecialchars($error); ?>

                </div>

            <?php endif; ?>


            <form method="POST">


                <!-- NAMA PRODUK -->

                <div class="form-group">

                    <label for="name">
                        Nama Produk
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?= htmlspecialchars($product['name']); ?>"
                        required
                    >

                </div>


                <!-- KATEGORI -->

                <div class="form-group">

                    <label for="category_id">
                        Kategori
                    </label>

                    <select
                        id="category_id"
                        name="category_id"
                        required
                    >

                        <option value="">
                            -- Pilih Kategori --
                        </option>


                        <?php while ($category = mysqli_fetch_assoc($category_query)): ?>

                            <option
                                value="<?= $category['id']; ?>"
                                <?= (
                                    $product['category_id']
                                    == $category['id']
                                ) ? 'selected' : ''; ?>
                            >

                                <?= htmlspecialchars($category['name']); ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>


                <!-- DESKRIPSI -->

                <div class="form-group">

                    <label for="description">
                        Deskripsi
                    </label>

                    <textarea
                        id="description"
                        name="description"
                    ><?= htmlspecialchars($product['description'] ?? ''); ?></textarea>

                </div>


                <!-- HARGA -->

                <div class="form-group">

                    <label for="price">
                        Harga
                    </label>

                    <input
                        type="number"
                        id="price"
                        name="price"
                        min="0"
                        value="<?= htmlspecialchars($product['price']); ?>"
                        required
                    >

                </div>


                <!-- STOK -->

                <div class="form-group">

                    <label for="stock">
                        Stok
                    </label>

                    <input
                        type="number"
                        id="stock"
                        name="stock"
                        min="0"
                        value="<?= htmlspecialchars($product['stock']); ?>"
                        required
                    >

                </div>


                <!-- TOMBOL -->

                <div class="buttons">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Simpan Perubahan
                    </button>


                    <a
                        href="produk.php"
                        class="btn btn-secondary"
                    >
                        Batal
                    </a>

                </div>


            </form>

        </div>

    </div>


</body>

</html>