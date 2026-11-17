<?php

require_once "config/database.php";

?>

<?php include "includes/header.php"; ?>

<!-- =========================
     HERO
========================= -->

<section class="hero">

    <div class="container">

        <div class="hero-content">

            <h1>
                Selamat Datang di Toko Kita
            </h1>

            <p>
                Menyediakan berbagai kebutuhan sembako,
                minuman, makanan ringan, serta menu
                warung kopi untuk menemani hari Anda.
            </p>

            <a href="produk.php" class="btn">
             Lihat Produk
            </a>

        </div>

    </div>

</section>


<!-- =========================
     PRODUK
========================= -->

<section class="section" id="produk">

    <div class="container">

        <div class="section-title">

            <h2>
                Produk Kami
            </h2>

            <p>
                Beberapa produk yang tersedia di toko kami
            </p>

        </div>


        <div class="product-grid">

            <?php

            $query = "
                SELECT 
                    products.*,
                    categories.name AS category_name
                FROM products

                INNER JOIN categories
                ON products.category_id = categories.id

                ORDER BY products.id DESC

                LIMIT 8
            ";

            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) > 0):

                while ($product = mysqli_fetch_assoc($result)):

            ?>

                <div class="product-card">

                    <?php if (!empty($product['image'])): ?>

                        <img
                            src="assets/images/<?php echo htmlspecialchars($product['image']); ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                            class="product-image"
                        >

                    <?php else: ?>

                        <div class="product-image"></div>

                    <?php endif; ?>


                    <div class="product-info">

                        <small>
                            <?php echo htmlspecialchars($product['category_name']); ?>
                        </small>

                        <h3>
                            <?php echo htmlspecialchars($product['name']); ?>
                        </h3>

                        <div class="product-price">

                            Rp <?php echo number_format(
                                $product['price'],
                                0,
                                ',',
                                '.'
                            ); ?>

                        </div>

                        <div class="product-stock">

                            Stok:
                            <?php echo $product['stock']; ?>

                        </div>

                    </div>

                </div>

            <?php

                endwhile;

            else:

            ?>

                <p>
                    Belum ada produk.
                </p>

            <?php endif; ?>

        </div>

    </div>

</section>


<!-- =========================
     TENTANG TOKO
========================= -->

<section class="section">

    <div class="container">

        <div class="section-title">

            <h2>
                Tentang Toko Kami
            </h2>

        </div>

        <p style="text-align:center; line-height:1.8;">

            Toko kami menyediakan berbagai kebutuhan
            sehari-hari sekaligus menjadi tempat bersantai
            menikmati kopi dan makanan ringan.

        </p>

    </div>

</section>


<?php include "includes/footer.php"; ?>