<?php

require_once "config/database.php";


/*
|--------------------------------------------------------------------------
| CEK ID PRODUK
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    header("Location: produk.php");
    exit;

}


$productId = (int) $_GET['id'];


/*
|--------------------------------------------------------------------------
| AMBIL PRODUK
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        products.*,
        categories.name AS category_name
    FROM products

    INNER JOIN categories
        ON products.category_id = categories.id

    WHERE products.id = ?
";


$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $productId
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


/*
|--------------------------------------------------------------------------
| CEK PRODUK
|--------------------------------------------------------------------------
*/

if (mysqli_num_rows($result) === 0) {

    header("Location: produk.php");
    exit;

}


$product = mysqli_fetch_assoc($result);

?>

<?php include "includes/header.php"; ?>


<section class="section">

    <div class="container">

        <div class="product-detail">


            <!-- GAMBAR -->

            <div class="product-detail-image">

                <?php if (!empty($product['image'])): ?>

                    <img
                        src="assets/images/<?php echo htmlspecialchars($product['image']); ?>"
                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                    >

                <?php else: ?>

                    <div class="product-detail-placeholder">
                        Tidak ada gambar
                    </div>

                <?php endif; ?>

            </div>


            <!-- INFORMASI -->

            <div class="product-detail-info">


                <small>

                    <?php
                    echo htmlspecialchars(
                        $product['category_name']
                    );
                    ?>

                </small>


                <h1>

                    <?php
                    echo htmlspecialchars(
                        $product['name']
                    );
                    ?>

                </h1>


                <div class="detail-price">

                    Rp
                    <?php
                    echo number_format(
                        $product['price'],
                        0,
                        ',',
                        '.'
                    );
                    ?>

                </div>


                <p class="detail-description">

                    <?php

                    if (!empty($product['description'])) {

                        echo nl2br(
                            htmlspecialchars(
                                $product['description']
                            )
                        );

                    } else {

                        echo "Belum ada deskripsi produk.";

                    }

                    ?>

                </p>


                <!-- STOK -->

                <?php if ($product['stock'] > 0): ?>

                    <div class="detail-stock stock-available">

                        🟢 Produk tersedia

                    </div>

                    <p>
                        Stok:
                        <strong>
                            <?php echo $product['stock']; ?>
                        </strong>
                    </p>


                    <form
    action="keranjang.php"
    method="POST"
    class="add-cart-form"
>

    <input
        type="hidden"
        name="action"
        value="add"
    >

    <input
        type="hidden"
        name="product_id"
        value="<?php echo $product['id']; ?>"
    >

    <label for="quantity">
        Jumlah:
    </label>

    <input
        type="number"
        name="quantity"
        id="quantity"
        value="1"
        min="1"
        max="<?php echo $product['stock']; ?>"
    >

    <button
        type="submit"
        class="btn"
    >
        🛒 Tambah ke Keranjang
    </button>

</form>

                <?php else: ?>

                    <div class="detail-stock stock-empty">

                        🔴 Produk sedang habis

                    </div>

                <?php endif; ?>


                <br><br>


                <a
                    href="produk.php"
                    class="btn btn-secondary"
                >
                    ← Kembali ke Produk
                </a>


            </div>

        </div>

    </div>

</section>


<?php include "includes/footer.php"; ?>