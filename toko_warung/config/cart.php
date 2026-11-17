<?php

/*
|--------------------------------------------------------------------------
| HITUNG JUMLAH ITEM DI KERANJANG
|--------------------------------------------------------------------------
*/

function getCartCount()
{
    if (!isset($_SESSION['cart'])) {
        return 0;
    }

    return array_sum($_SESSION['cart']);
}


/*
|--------------------------------------------------------------------------
| HITUNG TOTAL HARGA KERANJANG
|--------------------------------------------------------------------------
*/

function getCartTotal($conn)
{
    if (
        !isset($_SESSION['cart']) ||
        empty($_SESSION['cart'])
    ) {
        return 0;
    }


    $total = 0;


    foreach ($_SESSION['cart'] as $productId => $quantity) {

        $productId = (int) $productId;
        $quantity = (int) $quantity;


        $sql = "
            SELECT price
            FROM products
            WHERE id = ?
        ";


        $stmt = mysqli_prepare(
            $conn,
            $sql
        );


        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $productId
        );


        mysqli_stmt_execute($stmt);


        $result = mysqli_stmt_get_result(
            $stmt
        );


        if ($product = mysqli_fetch_assoc($result)) {

            $total +=
                $product['price'] * $quantity;

        }

    }


    return $total;
}


/*
|--------------------------------------------------------------------------
| FORMAT RUPIAH
|--------------------------------------------------------------------------
*/

function rupiah($number)
{
    return "Rp " . number_format(
        $number,
        0,
        ',',
        '.'
    );
}

?>