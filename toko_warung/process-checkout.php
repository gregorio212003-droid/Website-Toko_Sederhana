<?php

require_once "config/database.php";
require_once "config/cart.php";


/*
|--------------------------------------------------------------------------
| HANYA BOLEH POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: produk.php");

    exit;

}


/*
|--------------------------------------------------------------------------
| CEK CART
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['cart']) ||
    empty($_SESSION['cart'])
) {

    header("Location: produk.php");

    exit;

}


/*
|--------------------------------------------------------------------------
| AMBIL DATA CUSTOMER
|--------------------------------------------------------------------------
*/

$customerName =
    trim($_POST['customer_name'] ?? '');

$customerPhone =
    trim($_POST['customer_phone'] ?? '');

$customerAddress =
    trim($_POST['customer_address'] ?? '');

$notes =
    trim($_POST['notes'] ?? '');


/*
|--------------------------------------------------------------------------
| VALIDASI
|--------------------------------------------------------------------------
*/

if (
    $customerName === '' ||
    $customerPhone === ''
) {

    die(
        "Nama dan nomor WhatsApp wajib diisi."
    );

}


/*
|--------------------------------------------------------------------------
| KERANJANG
|--------------------------------------------------------------------------
*/

$cart =
    $_SESSION['cart'];


/*
|--------------------------------------------------------------------------
| VALIDASI STOK + HITUNG TOTAL
|--------------------------------------------------------------------------
*/

$total = 0;

$orderItems = [];


foreach ($cart as $productId => $quantity) {

    $productId =
        (int) $productId;

    $quantity =
        (int) $quantity;


    if ($quantity <= 0) {
        continue;
    }


    $sql = "
        SELECT
            id,
            name,
            price,
            stock
        FROM products
        WHERE id = ?
    ";


    $stmt =
        mysqli_prepare(
            $conn,
            $sql
        );


    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $productId
    );


    mysqli_stmt_execute(
        $stmt
    );


    $result =
        mysqli_stmt_get_result(
            $stmt
        );


    $product =
        mysqli_fetch_assoc(
            $result
        );


    /*
    |--------------------------------------------------------------------------
    | PRODUK TIDAK ADA
    |--------------------------------------------------------------------------
    */

    if (!$product) {

        die(
            "Salah satu produk tidak ditemukan."
        );

    }


    /*
    |--------------------------------------------------------------------------
    | STOK TIDAK CUKUP
    |--------------------------------------------------------------------------
    */

    if (
        $quantity >
        $product['stock']
    ) {

        die(
            "Stok produk "
            . htmlspecialchars($product['name'])
            . " tidak mencukupi."
        );

    }


    /*
    |--------------------------------------------------------------------------
    | SUBTOTAL
    |--------------------------------------------------------------------------
    */

    $subtotal =
        $product['price']
        * $quantity;


    $total += $subtotal;


    /*
    |--------------------------------------------------------------------------
    | SIMPAN DATA ITEM
    |--------------------------------------------------------------------------
    */

    $orderItems[] = [

        'product_id' =>
            $product['id'],

        'product_name' =>
            $product['name'],

        'price' =>
            $product['price'],

        'quantity' =>
            $quantity,

        'subtotal' =>
            $subtotal

    ];

}


/*
|--------------------------------------------------------------------------
| CEK TOTAL
|--------------------------------------------------------------------------
*/

if ($total <= 0) {

    die(
        "Keranjang tidak valid."
    );

}


/*
|--------------------------------------------------------------------------
| MULAI TRANSAKSI
|--------------------------------------------------------------------------
*/

mysqli_begin_transaction($conn);


try {


    /*
    |--------------------------------------------------------------------------
    | INSERT ORDERS
    |--------------------------------------------------------------------------
    */

    $sql = "
        INSERT INTO orders
        (
            customer_name,
            customer_phone,
            customer_address,
            notes,
            total,
            status
        )

        VALUES
        (?, ?, ?, ?, ?, 'Menunggu')
    ";


    $stmt =
        mysqli_prepare(
            $conn,
            $sql
        );


    mysqli_stmt_bind_param(
        $stmt,
        "ssssd",
        $customerName,
        $customerPhone,
        $customerAddress,
        $notes,
        $total
    );


    mysqli_stmt_execute(
        $stmt
    );


    /*
    |--------------------------------------------------------------------------
    | ID ORDER
    |--------------------------------------------------------------------------
    */

    $orderId =
        mysqli_insert_id($conn);


    /*
    |--------------------------------------------------------------------------
    | INSERT ORDER DETAILS
    |--------------------------------------------------------------------------
    */

    $detailSql = "
        INSERT INTO order_details
        (
            order_id,
            product_id,
            product_name,
            price,
            quantity,
            subtotal
        )

        VALUES
        (?, ?, ?, ?, ?, ?)
    ";


    foreach ($orderItems as $item) {

        $detailStmt =
            mysqli_prepare(
                $conn,
                $detailSql
            );


        mysqli_stmt_bind_param(
            $detailStmt,
            "iisdid",
            $orderId,
            $item['product_id'],
            $item['product_name'],
            $item['price'],
            $item['quantity'],
            $item['subtotal']
        );


        mysqli_stmt_execute(
            $detailStmt
        );

    }


    /*
    |--------------------------------------------------------------------------
    | KURANGI STOK
    |--------------------------------------------------------------------------
    */

    $stockSql = "
        UPDATE products

        SET stock = stock - ?

        WHERE id = ?

        AND stock >= ?
    ";


    foreach ($orderItems as $item) {

        $stockStmt =
            mysqli_prepare(
                $conn,
                $stockSql
            );


        $quantity =
            $item['quantity'];

        $productId =
            $item['product_id'];


        mysqli_stmt_bind_param(
            $stockStmt,
            "iii",
            $quantity,
            $productId,
            $quantity
        );


        mysqli_stmt_execute(
            $stockStmt
        );


        /*
        | Pastikan stok berhasil dikurangi
        */

        if (
            mysqli_stmt_affected_rows(
                $stockStmt
            ) !== 1
        ) {

            throw new Exception(
                "Stok produk berubah."
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | COMMIT
    |--------------------------------------------------------------------------
    */

    mysqli_commit($conn);


} catch (Exception $e) {


    /*
    |--------------------------------------------------------------------------
    | ROLLBACK
    |--------------------------------------------------------------------------
    */

    mysqli_rollback($conn);


    die(
        "Checkout gagal: "
        . htmlspecialchars(
            $e->getMessage()
        )
    );

}


/*
|--------------------------------------------------------------------------
| BUAT PESAN WHATSAPP
|--------------------------------------------------------------------------
*/

$message = "Halo, saya ingin melakukan pemesanan.\n\n";

$message .=
    "No. Pesanan: #"
    . $orderId
    . "\n";

$message .=
    "Nama: "
    . $customerName
    . "\n";

$message .=
    "No. HP: "
    . $customerPhone
    . "\n\n";


$message .= "Pesanan:\n";


foreach ($orderItems as $item) {

    $message .=
        "- "
        . $item['product_name']
        . " x "
        . $item['quantity']
        . " = Rp"
        . number_format(
            $item['subtotal'],
            0,
            ',',
            '.'
        )
        . "\n";

}


$message .=
    "\nTotal: Rp"
    . number_format(
        $total,
        0,
        ',',
        '.'
    )
    . "\n";


if ($customerAddress !== '') {

    $message .=
        "\nAlamat: "
        . $customerAddress
        . "\n";

}


if ($notes !== '') {

    $message .=
        "Catatan: "
        . $notes
        . "\n";

}


/*
|--------------------------------------------------------------------------
| NOMOR WHATSAPP TOKO
|--------------------------------------------------------------------------
|
| GANTI DENGAN NOMOR WHATSAPP TOKO KAMU.
|
| Contoh:
| 08123456789
|
| ditulis menjadi:
| 628123456789
|
*/

$storeWhatsApp =
    "6282250020269";


/*
|--------------------------------------------------------------------------
| BUAT URL WHATSAPP
|--------------------------------------------------------------------------
*/

$whatsappUrl =
    "https://wa.me/"
    . $storeWhatsApp
    . "?text="
    . urlencode($message);


/*
|--------------------------------------------------------------------------
| HAPUS CART
|--------------------------------------------------------------------------
*/

$_SESSION['cart'] = [];


/*
|--------------------------------------------------------------------------
| REDIRECT KE WHATSAPP
|--------------------------------------------------------------------------
*/

header(
    "Location: "
    . $whatsappUrl
);

exit;

?>