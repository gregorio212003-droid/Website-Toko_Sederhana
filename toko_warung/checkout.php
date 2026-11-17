<?php

require_once "config/database.php";
require_once "config/cart.php";


/*
|--------------------------------------------------------------------------
| CEK KERANJANG
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['cart']) ||
    empty($_SESSION['cart'])
) {

    header("Location: produk.php");

    exit;

}


$total =
    getCartTotal($conn);

?>

<?php include "includes/header.php"; ?>


<section class="page-header">

    <div class="container">

        <h1>
            Checkout
        </h1>

        <p>
            Lengkapi informasi pemesanan Anda.
        </p>

    </div>

</section>


<section class="section">

    <div class="container">


        <div class="checkout-grid">


            <!-- =========================
                 FORM CUSTOMER
            ========================== -->

            <div class="checkout-form">

                <h2>
                    Informasi Pelanggan
                </h2>


                <form
                    action="process-checkout.php"
                    method="POST"
                >


                    <div class="form-group">

                        <label>
                            Nama Lengkap
                        </label>

                        <input
                            type="text"
                            name="customer_name"
                            required
                            placeholder="Masukkan nama Anda"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Nomor WhatsApp
                        </label>

                        <input
                            type="text"
                            name="customer_phone"
                            required
                            placeholder="Contoh: 08123456789"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Alamat
                        </label>

                        <textarea
                            name="customer_address"
                            rows="4"
                            placeholder="Masukkan alamat jika diperlukan"
                        ></textarea>

                    </div>


                    <div class="form-group">

                        <label>
                            Catatan Pesanan
                        </label>

                        <textarea
                            name="notes"
                            rows="4"
                            placeholder="Contoh: Antar sore hari"
                        ></textarea>

                    </div>


                    <button
                        type="submit"
                        class="btn checkout-submit"
                    >
                        💬 Checkout via WhatsApp
                    </button>


                </form>

            </div>


            <!-- =========================
                 RINGKASAN
            ========================== -->

            <div class="checkout-summary">

                <h2>
                    Ringkasan Pesanan
                </h2>


                <?php

                foreach (
                    $_SESSION['cart']
                    as $productId => $quantity
                ):

                    $productId =
                        (int) $productId;


                    $sql = "
                        SELECT
                            name,
                            price
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
                        continue;
                    }


                    $subtotal =
                        $product['price']
                        * $quantity;

                ?>

                    <div class="checkout-item">

                        <div>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $product['name']
                                );
                                ?>
                            </strong>

                            <small>
                                <?php echo $quantity; ?>
                                ×
                                Rp
                                <?php
                                echo number_format(
                                    $product['price'],
                                    0,
                                    ',',
                                    '.'
                                );
                                ?>
                            </small>

                        </div>


                        <strong>

                            Rp
                            <?php
                            echo number_format(
                                $subtotal,
                                0,
                                ',',
                                '.'
                            );
                            ?>

                        </strong>

                    </div>


                <?php endforeach; ?>


                <hr>


                <div class="checkout-total">

                    <span>
                        Total
                    </span>

                    <strong>

                        Rp
                        <?php
                        echo number_format(
                            $total,
                            0,
                            ',',
                            '.'
                        );
                        ?>

                    </strong>

                </div>

            </div>


        </div>

    </div>

</section>


<?php include "includes/footer.php"; ?>