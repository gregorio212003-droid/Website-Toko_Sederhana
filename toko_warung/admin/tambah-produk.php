<?php

require_once "../config/database.php";
require_once "../includes/admin-auth.php";

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $stock = $_POST['stock'] ?? '';
    $category_id = $_POST['category_id'] ?? '';

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

        $sql = "
            INSERT INTO products
            (category_id, name, description, price, stock)
            VALUES (?, ?, ?, ?, ?)
        ";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {

            $error = "Query gagal: " . mysqli_error($conn);

        } else {

            $price = (float) $price;
            $stock = (int) $stock;
            $category_id = (int) $category_id;

            mysqli_stmt_bind_param(
                $stmt,
                "issdi",
                $category_id,
                $name,
                $description,
                $price,
                $stock
            );

            if (mysqli_stmt_execute($stmt)) {

                header("Location: produk.php");
                exit;

            } else {

                $error = "Produk gagal ditambahkan: " . mysqli_stmt_error($stmt);

            }

            mysqli_stmt_close($stmt);
        }
    }
}


/*
|--------------------------------------------------------------------------
| Ambil daftar kategori
|--------------------------------------------------------------------------
*/

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

    <title>Tambah Produk - Admin</title>

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

            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
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

        <h2>🛒 Admin Toko Warung</h2>

        <a href="logout.php" class="logout">
            Logout
        </a>

    </div>


    <div class="container">

        <div class="card">

            <h1>Tambah Produk</h1>


            <?php if ($error !== ''): ?>

                <div class="error">
                    <?= htmlspecialchars($error); ?>
                </div>

            <?php endif; ?>


            <form method="POST">

                <div class="form-group">

                    <label for="name">
                        Nama Produk
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Contoh: Indomie Goreng"
                        value="<?= htmlspecialchars($_POST['name'] ?? ''); ?>"
                        required
                    >

                </div>


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
                                    ($_POST['category_id'] ?? '') == $category['id']
                                ) ? 'selected' : ''; ?>
                            >

                                <?= htmlspecialchars($category['name']); ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>


                <div class="form-group">

                    <label for="description">
                        Deskripsi
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        placeholder="Deskripsi produk..."
                    ><?= htmlspecialchars($_POST['description'] ?? ''); ?></textarea>

                </div>


                <div class="form-group">

                    <label for="price">
                        Harga
                    </label>

                    <input
                        type="number"
                        id="price"
                        name="price"
                        min="0"
                        placeholder="Contoh: 3500"
                        value="<?= htmlspecialchars($_POST['price'] ?? ''); ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="stock">
                        Stok
                    </label>

                    <input
                        type="number"
                        id="stock"
                        name="stock"
                        min="0"
                        placeholder="Contoh: 20"
                        value="<?= htmlspecialchars($_POST['stock'] ?? ''); ?>"
                        required
                    >

                </div>


                <div class="buttons">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Simpan Produk
                    </button>

                    <a
                        href="produk.php"
                        class="btn btn-secondary"
                    >
                        Kembali
                    </a>

                </div>

            </form>

        </div>

    </div>

</body>

</html>