<?php

require_once "../config/database.php";
require_once "../includes/admin-auth.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');

    if ($name === '') {

        $error = "Nama kategori wajib diisi.";

    } else {

        $sql = "
            INSERT INTO categories (name)
            VALUES (?)
        ";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {

            $error = "Query gagal: " . mysqli_error($conn);

        } else {

            mysqli_stmt_bind_param(
                $stmt,
                "s",
                $name
            );

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header("Location: kategori.php");
                exit;

            } else {

                $error = "Kategori gagal ditambahkan: "
                       . mysqli_stmt_error($stmt);

                mysqli_stmt_close($stmt);
            }
        }
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

    <title>Tambah Kategori</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;

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
                0 3px 10px rgba(0,0,0,.08);
        }

        h1 {
            margin-top: 0;

            color: #333;
        }

        label {
            display: block;

            margin-bottom: 8px;

            font-weight: bold;
        }

        input {
            width: 100%;

            padding: 12px;

            border: 1px solid #ccc;

            border-radius: 6px;

            font-size: 15px;
        }

        .buttons {
            margin-top: 20px;

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

        <strong>
            🛒 Admin Toko Warung
        </strong>

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
                Tambah Kategori
            </h1>


            <?php if ($error !== ''): ?>

                <div class="error">

                    <?= htmlspecialchars($error); ?>

                </div>

            <?php endif; ?>


            <form method="POST">

                <label for="name">
                    Nama Kategori
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Contoh: Minuman"
                    required
                >


                <div class="buttons">

                    <button type="submit">
                        Simpan
                    </button>

                    <a
                        href="kategori.php"
                        class="back"
                    >
                        Kembali
                    </a>

                </div>

            </form>

        </div>

    </div>

</body>

</html>