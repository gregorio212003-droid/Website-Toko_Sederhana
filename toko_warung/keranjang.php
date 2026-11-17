<?php

require_once "config/database.php";
require_once "config/cart.php";


/*
|--------------------------------------------------------------------------
| INISIALISASI CART
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}


/*
|--------------------------------------------------------------------------
| PROSES TAMBAH PRODUK
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action'])
) {

    $action = $_POST['action'];


    /*
    |--------------------------------------------------------------------------
    | TAMBAH
    |--------------------------------------------------------------------------
    */

    if ($action === 'add') {

        $productId = isset($_POST['product_id'])
            ? (int) $_POST['product_id']
            : 0;

        $quantity = isset($_POST['quantity'])
            ? (int) $_POST['quantity']
            : 1;


        if ($quantity < 1) {
            $quantity = 1;
        }


        /*
        | Ambil produk
        */

        $sql = "
            SELECT id, name, stock
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


        $product = mysqli_fetch_assoc(
            $result
        );


        /*
        | Produk ditemukan?
        */

        if ($product) {

            /*
            | Cek stok
            */

            if ($product['stock'] > 0) {

                $currentQuantity =
                    $_SESSION['cart'][$productId]
                    ?? 0;


                $newQuantity =
                    $currentQuantity + $quantity;


                /*
                | Jangan melebihi stok
                */

                if ($newQuantity > $product['stock']) {

                    $newQuantity =
                        $product['stock'];

                }


                $_SESSION['cart'][$productId] =
                    $newQuantity;

            }

        }


        header(
            "Location: keranjang.php"
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE JUMLAH
    |--------------------------------------------------------------------------
    */

    if ($action === 'update') {

        if (
            isset($_POST['quantities']) &&
            is_array($_POST['quantities'])
        ) {

            foreach (
                $_POST['quantities']
                as $productId => $quantity
            ) {

                $productId =
                    (int) $productId;

                $quantity =
                    (int) $quantity;


                /*
                | Ambil stok terbaru
                */

                $sql = "
                    SELECT stock
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


                if (!$product) {
                    unset(
                        $_SESSION['cart'][$productId]
                    );
                    continue;
                }


                /*
                | Kalau jumlah <= 0
                | hapus
                */

                if ($quantity <= 0) {

                    unset(
                        $_SESSION['cart'][$productId]
                    );

                    continue;

                }


                /*
                | Jangan melebihi stok
                */

                if (
                    $quantity >
                    $product['stock']
                ) {

                    $quantity =
                        $product['stock'];

                }


                $_SESSION['cart'][$productId] =
                    $quantity;

            }

        }


        header(
            "Location: keranjang.php"
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | HAPUS PRODUK
    |--------------------------------------------------------------------------
    */

    if ($action === 'remove') {

        $productId =
            isset($_POST['product_id'])
            ? (int) $_POST['product_id']
            : 0;


        unset(
            $_SESSION['cart'][$productId]
        );


        header(
            "Location: keranjang.php"
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | KOSONGKAN KERANJANG
    |--------------------------------------------------------------------------
    */

    if ($action === 'clear') {

        $_SESSION['cart'] = [];


        header(
            "Location: keranjang.php"
        );

        exit;
    }

}


/*
|--------------------------------------------------------------------------
| AMBIL DATA PRODUK DALAM CART
|--------------------------------------------------------------------------
*/

$cartProducts = [];


if (!empty($_SESSION['cart'])) {

    foreach (
        $_SESSION['cart']
        as $productId => $quantity
    ) {

        $productId =
            (int) $productId;


        $sql = "
            SELECT
                products.*,
                categories.name AS category_name
            FROM products

            INNER JOIN categories
                ON products.category_id =
                   categories.id

            WHERE products.id = ?
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


        if ($product) {

            /*
            | Kalau stok berubah,
            | sesuaikan cart
            */

            if (
                $quantity >
                $product['stock']
            ) {

                $quantity =
                    $product['stock'];

                $_SESSION['cart'][$productId] =
                    $quantity;

            }


            if ($quantity > 0) {

                $product['quantity'] =
                    $quantity;


                $product['subtotal'] =
                    $product['price'] *
                    $quantity;


                $cartProducts[] =
                    $product;

            } else {

                unset(
                    $_SESSION['cart'][$productId]
                );

            }

        } else {

            unset(
                $_SESSION['cart'][$productId]
            );

        }

    }

}


$total =
    getCartTotal($conn);

?>

<?php include "includes/header.php"; ?>


<section class="page-header">

    <div class="container">

        <h1>
            Keranjang Belanja
        </h1>

        <p>
            Periksa kembali produk yang ingin Anda pesan.
        </p>

    </div>

</section>


<section class="section">

    <div class="container">


        <?php if (empty($cartProducts)): ?>


            <div class="empty-cart">

                <h2>
                    🛒 Keranjang masih kosong
                </h2>

                <p>
                    Silakan pilih produk terlebih dahulu.
                </p>

                <br>

                <a
                    href="produk.php"
                    class="btn"
                >
                    Belanja Sekarang
                </a>

            </div>


        <?php else: ?>


            <form
                action="keranjang.php"
                method="POST"
            >

                <input
                    type="hidden"
                    name="action"
                    value="update"
                >


                <div class="cart-container">


                    <!-- =========================
                         ITEM
                    ========================== -->

                    <div class="cart-items">


                        <?php foreach (
                            $cartProducts
                            as $product
                        ): ?>


                            <div class="cart-item">


                                <!-- GAMBAR -->

                                <div class="cart-image">

                                    <?php if (
                                        !empty(
                                            $product['image']
                                        )
                                    ): ?>

                                        <img
                                            src="assets/images/<?php echo htmlspecialchars($product['image']); ?>"
                                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                                        >

                                    <?php else: ?>

                                        <div class="cart-placeholder">
                                            Tidak ada gambar
                                        </div>

                                    <?php endif; ?>

                                </div>


                                <!-- INFO -->

                                <div class="cart-info">

                                    <small>
                                        <?php
                                        echo htmlspecialchars(
                                            $product['category_name']
                                        );
                                        ?>
                                    </small>

                                    <h3>
                                        <?php
                                        echo htmlspecialchars(
                                            $product['name']
                                        );
                                        ?>
                                    </h3>

                                    <p>
                                        Rp
                                        <?php
                                        echo number_format(
                                            $product['price'],
                                            0,
                                            ',',
                                            '.'
                                        );
                                        ?>
                                    </p>

                                </div>


                                <!-- QUANTITY -->

                                <div class="cart-quantity">

                                    <input
                                        type="number"
                                        name="quantities[<?php echo $product['id']; ?>]"
                                        value="<?php echo $product['quantity']; ?>"
                                        min="0"
                                        max="<?php echo $product['stock']; ?>"
                                    >

                                </div>


                                <!-- SUBTOTAL -->

                                <div class="cart-subtotal">

                                    Rp
                                    <?php
                                    echo number_format(
                                        $product['subtotal'],
                                        0,
                                        ',',
                                        '.'
                                    );
                                    ?>

                                </div>


                                <!-- HAPUS -->

                                <div class="cart-remove">

                                    <button
                                        type="submit"
                                        form="remove-<?php echo $product['id']; ?>"
                                        class="remove-button"
                                    >
                                        Hapus
                                    </button>

                                </div>


                            </div>


                        <?php endforeach; ?>


                    </div>


                </div>


                <!-- =========================
                     TOTAL
                ========================== -->

                <div class="cart-summary">

                    <div>

                        <strong>
                            Total Belanja
                        </strong>

                        <span>
                            Rp
                            <?php
                            echo number_format(
                                $total,
                                0,
                                ',',
                                '.'
                            );
                            ?>
                        </span>

                    </div>


                    <button
                        type="submit"
                        class="btn"
                    >
                        Update Keranjang
                    </button>


                    <a
                        href="checkout.php"
                        class="btn checkout-button"
                    >
                        Lanjut ke Checkout
                    </a>

                </div>

            </form>


            <!-- FORM HAPUS -->

            <?php foreach (
                $cartProducts
                as $product
            ): ?>

                <form
                    id="remove-<?php echo $product['id']; ?>"
                    action="keranjang.php"
                    method="POST"
                    style="display:none;"
                >

                    <input
                        type="hidden"
                        name="action"
                        value="remove"
                    >

                    <input
                        type="hidden"
                        name="product_id"
                        value="<?php echo $product['id']; ?>"
                    >

                </form>

            <?php endforeach; ?>


            <!-- KOSONGKAN -->

            <form
                action="keranjang.php"
                method="POST"
                style="margin-top:15px;"
            >

                <input
                    type="hidden"
                    name="action"
                    value="clear"
                >

                <button
                    type="submit"
                    class="btn btn-danger"
                >
                    Kosongkan Keranjang
                </button>

            </form>


        <?php endif; ?>


    </div>

</section>


<?php include "includes/footer.php"; ?>