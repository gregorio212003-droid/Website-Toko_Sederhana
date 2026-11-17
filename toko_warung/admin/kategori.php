<?php

require_once "../config/database.php";
require_once "../includes/admin-auth.php";

$sql = "
    SELECT id, name
    FROM categories
    ORDER BY id DESC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
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

    <title>Kelola Kategori - Admin</title>

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
            max-width: 900px;

            margin: 30px auto;

            padding: 0 20px;
        }

        .header {
            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 20px;
        }

        .header h1 {
            color: #333;
        }

        .btn {
            display: inline-block;

            padding: 10px 15px;

            border-radius: 6px;

            text-decoration: none;

            border: none;

            cursor: pointer;
        }

        .btn-primary {
            background: #0d6efd;
            color: white;
        }

        .btn-warning {
            background: #ffc107;
            color: #222;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        table {
            width: 100%;

            border-collapse: collapse;

            background: white;

            box-shadow:
                0 3px 10px rgba(0, 0, 0, 0.08);
        }

        th,
        td {
            padding: 14px;

            border-bottom: 1px solid #ddd;

            text-align: left;
        }

        th {
            background: #198754;
            color: white;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .actions {
            white-space: nowrap;
        }

        .empty {
            text-align: center;

            padding: 30px;

            color: #777;
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

        <div class="header">

            <h1>
                Kelola Kategori
            </h1>

            <a
                href="tambah-kategori.php"
                class="btn btn-primary"
            >
                + Tambah Kategori
            </a>

        </div>


        <table>

            <thead>

                <tr>

                    <th>No</th>

                    <th>Nama Kategori</th>

                    <th>Aksi</th>

                </tr>

            </thead>


            <tbody>

                <?php if (mysqli_num_rows($result) > 0): ?>

                    <?php $no = 1; ?>

                    <?php while ($category = mysqli_fetch_assoc($result)): ?>

                        <tr>

                            <td>
                                <?= $no++; ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($category['name']); ?>
                            </td>

                            <td class="actions">

                                <a
                                    href="edit-kategori.php?id=<?= $category['id']; ?>"
                                    class="btn btn-warning"
                                >
                                    Edit
                                </a>

                                <a
                                    href="hapus-kategori.php?id=<?= $category['id']; ?>"
                                    class="btn btn-danger"
                                    onclick="return confirm('Yakin ingin menghapus kategori ini?');"
                                >
                                    Hapus
                                </a>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td
                            colspan="3"
                            class="empty"
                        >
                            Belum ada kategori.
                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</body>

</html>