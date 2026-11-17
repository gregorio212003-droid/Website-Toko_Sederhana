<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../config/database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {

        $error = "Username dan password wajib diisi.";

    } else {

        $sql = "
            SELECT id, username, password, name
            FROM admin_users
            WHERE username = ?
            LIMIT 1
        ";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            die("Query gagal: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $admin = mysqli_fetch_assoc($result);

        if ($admin && password_verify($password, $admin['password'])) {

            session_regenerate_id(true);

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_name'] = $admin['name'];

            header("Location: index.php");
            exit;

        } else {

            $error = "Username atau password salah.";

        }

        mysqli_stmt_close($stmt);
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login Admin - Toko Warung</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #198754, #0d6efd);
            min-height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;

            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 420px;

            background: white;
            padding: 35px;

            border-radius: 15px;

            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .logo {
            text-align: center;
            font-size: 45px;
            margin-bottom: 10px;
        }

        h1 {
            text-align: center;
            color: #222;
            margin-bottom: 8px;
        }

        .subtitle {
            text-align: center;
            color: #777;
            margin-bottom: 30px;
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
            padding: 13px;

            border: 1px solid #ccc;
            border-radius: 8px;

            font-size: 15px;
            outline: none;
        }

        input:focus {
            border-color: #198754;
        }

        .btn-login {
            width: 100%;

            padding: 13px;

            border: none;
            border-radius: 8px;

            background: #198754;
            color: white;

            font-size: 16px;
            font-weight: bold;

            cursor: pointer;
        }

        .btn-login:hover {
            background: #157347;
        }

        .error {
            background: #f8d7da;
            color: #842029;

            padding: 12px;
            border-radius: 8px;

            margin-bottom: 20px;

            text-align: center;
        }

        .footer {
            text-align: center;
            margin-top: 25px;

            color: #888;
            font-size: 13px;
        }

    </style>

</head>

<body>

    <div class="login-container">

        <div class="logo">🛒</div>

        <h1>Login Admin</h1>

        <p class="subtitle">
            Toko Warung
        </p>

        <?php if ($error !== ''): ?>

            <div class="error">
                <?= htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="form-group">

                <label for="username">
                    Username
                </label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Masukkan username"
                    required
                >

            </div>


            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Masukkan password"
                    required
                >

            </div>


            <button
                type="submit"
                class="btn-login"
            >
                LOGIN
            </button>

        </form>

        <div class="footer">
            © 2026 Toko Warung
        </div>

    </div>

</body>

</html>