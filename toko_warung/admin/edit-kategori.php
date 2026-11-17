<?php

require_once "../config/database.php";
require_once "../includes/admin-auth.php";


// =====================================================
// 1. CEK ID KATEGORI
// =====================================================

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die("ID kategori tidak valid.");
}


// =====================================================
// 2. AMBIL DATA KATEGORI
// =====================================================

$sql = "
    SELECT id, name
    FROM categories
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

$category = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$category) {
    die("Kategori tidak ditemukan.");
}


// =====================================================
// 3. PROSES UPDATE
// =====================================================

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');

    if ($name === '') {

        $error = "Nama kategori wajib diisi.";

    } else {

        $sql = "
            UPDATE categories
            SET name = ?
            WHERE id = ?
        ";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {

            $error = "Query gagal: " . mysqli_error($conn);

        } else {

            mysqli_stmt_bind_param(
                $stmt,
                "si",
                $name,
                $id
            );

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header("Location: kategori.php");
                exit;

            } else {

                $error = "Kategori gagal diperbarui: "
                       . mysqli_stmt_error($stmt);

                mysqli_stmt_close($stmt);
            }
        }
    }

    $category['name'] = $name;
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

    <title>Edit Kategori - Admin</title>


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
            max-width: 600px;

            margin: 40px auto;

            padding: 20px;
        }


        .card {
            background: white;

            padding: 30px;

            border-radius: 10px;

            box-shadow:
                0 3px 10px rgba(0, 0, 0, 0.08);
        }


        h1 {
            color: #333;

            margin-bottom: 25px;
        }


        .form-group {
            margin-bottom: 20px;
        }


        label {
            display: block;

            margin-bottom: 8px;

            font-weight: bold;

            color: #333;
        }


        input {
            width: 100%;

            padding: 12px;

            border: 1px solid #ccc;

            border-radius: 6px;

            font-size: 15px;
        }


        input:focus {
            outline: none;

            border-color: #198754;
        }


        .buttons {
            display: flex;

            gap: 10px;

            margin-top: 25px;
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
            background: #198754;

            color: white;
        }


        .back {
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
                Edit Kategori
            </h1>


            <?php if ($error !== ''): ?>

                <div class="error">

                    <?= htmlspecialchars($error); ?>

                </div>

            <?php endif; ?>


            <form method="POST">


                <div class="form-group">

                    <label for="name">
                        Nama Kategori
                    </label>


                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?= htmlspecialchars($category['name']); ?>"
                        required
                    >

                </div>


                <div class="buttons">

                    <button type="submit">
                        Simpan Perubahan
                    </button>


                    <a
                        href="kategori.php"
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