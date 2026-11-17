<?php

require_once "config/database.php";

/*
|--------------------------------------------------------------------------
| AMBIL DATA KATEGORI
|--------------------------------------------------------------------------
*/

$categoryQuery = "SELECT * FROM categories ORDER BY name ASC";

$categoryResult = mysqli_query($conn, $categoryQuery);


/*
|--------------------------------------------------------------------------
| AMBIL INPUT SEARCH
|--------------------------------------------------------------------------
*/

$keyword = isset($_GET['search'])
    ? trim($_GET['search'])
    : '';


/*
|--------------------------------------------------------------------------
| AMBIL INPUT CATEGORY
|--------------------------------------------------------------------------
*/

$category = isset($_GET['category'])
    ? (int) $_GET['category']
    : 0;


/*
|--------------------------------------------------------------------------
| MEMBUAT QUERY PRODUK
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        products.*,
        categories.name AS category_name
    FROM products

    INNER JOIN categories
        ON products.category_id = categories.id

    WHERE 1=1
";


/*
|--------------------------------------------------------------------------
| PARAMETER UNTUK PREPARED STATEMENT
|--------------------------------------------------------------------------
*/

$types = "";
$params = [];


/*
|--------------------------------------------------------------------------
| FILTER SEARCH
|--------------------------------------------------------------------------
*/

if ($keyword !== '') {

    $sql .= "
        AND products.name LIKE ?
    ";

    $types .= "s";

    $params[] = "%" . $keyword . "%";
}


/*
|--------------------------------------------------------------------------
| FILTER CATEGORY
|--------------------------------------------------------------------------
*/

if ($category > 0) {

    $sql .= "
        AND products.category_id = ?
    ";

    $types .= "i";

    $params[] = $category;
}


/*
|--------------------------------------------------------------------------
| URUTKAN PRODUK
|--------------------------------------------------------------------------
*/

$sql .= "
    ORDER BY products.id DESC
";


/*
|--------------------------------------------------------------------------
| PREPARED STATEMENT
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare($conn, $sql);


/*
|--------------------------------------------------------------------------
| BIND PARAMETER
|--------------------------------------------------------------------------
*/

if (!empty($params)) {

    mysqli_stmt_bind_param(
        $stmt,
        $types,
        ...$params
    );

}


/*
|--------------------------------------------------------------------------
| JALANKAN QUERY
|--------------------------------------------------------------------------
*/

mysqli_stmt_execute($stmt);


/*
|--------------------------------------------------------------------------
| AMBIL HASIL
|--------------------------------------------------------------------------
*/

$result = mysqli_stmt_get_result($stmt);

?>

<?php include "includes/header.php"; ?>


<!-- =====================================================
     HEADER HALAMAN
===================================================== -->

<section class="page-header">

    <div class="container">

        <h1>
            Produk Kami
        </h1>

        <p>
            Temukan berbagai kebutuhan sembako,
            minuman, makanan dan kopi.
        </p>

    </div>

</section>


<!-- =====================================================
     KATALOG
===================================================== -->

<section class="section">

    <div class="container">


        <!-- =================================================
             SEARCH & FILTER
        ================================================== -->

        <div class="product-filter">

            <form method="GET" action="produk.php">

                <div class="filter-row">


                    <!-- SEARCH -->

                    <div class="search-box">

                        <input
                            type="text"
                            name="search"
                            placeholder="Cari produk..."
                            value="<?php echo htmlspecialchars($keyword); ?>"
                        >

                    </div>


                    <!-- CATEGORY -->

                    <div class="category-box">

                        <select name="category">

                            <option value="0">
                                Semua Kategori
                            </option>


                            <?php while ($cat = mysqli_fetch_assoc($categoryResult)): ?>

                                <option
                                    value="<?php echo $cat['id']; ?>"
                                    <?php
                                    if ($category == $cat['id']) {
                                        echo 'selected';
                                    }
                                    ?>
                                >

                                    <?php
                                    echo htmlspecialchars($cat['name']);
                                    ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                    <!-- BUTTON -->

                    <button
                        type="submit"
                        class="btn"
                    >
                        Cari
                    </button>


                    <!-- RESET -->

                    <a
                        href="produk.php"
                        class="btn btn-secondary"
                    >
                        Reset
                    </a>

                </div>

            </form>

        </div>


        <!-- =================================================
             HASIL PENCARIAN
        ================================================== -->

        <div class="product-result-info">

            <?php if ($keyword !== ''): ?>

                <p>
                    Hasil pencarian:
                    <strong>
                        "<?php echo htmlspecialchars($keyword); ?>"
                    </strong>
                </p>

            <?php else: ?>

                <p>
                    Semua produk
                </p>

            <?php endif; ?>

        </div>


        <!-- =================================================
             PRODUCT GRID
        ================================================== -->

        <div class="product-grid">


            <?php if (mysqli_num_rows($result) > 0): ?>


                <?php while ($product = mysqli_fetch_assoc($result)): ?>


                    <div class="product-card">


                        <!-- GAMBAR -->

                        <?php if (!empty($product['image'])): ?>

                            <img
                                src="assets/images/<?php echo htmlspecialchars($product['image']); ?>"
                                alt="<?php echo htmlspecialchars($product['name']); ?>"
                                class="product-image"
                            >

                        <?php else: ?>

                            <div class="product-placeholder">
                                Tidak ada gambar
                            </div>

                        <?php endif; ?>


                        <!-- INFORMASI -->

                        <div class="product-info">


                            <small class="product-category">

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


                            <div class="product-price">

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


                            <!-- STATUS STOK -->

                            <?php

                            if ($product['stock'] <= 0) {

                                echo '<div class="stock stock-empty">
                                        🔴 Stok Habis
                                      </div>';

                            } elseif ($product['stock'] <= 5) {

                                echo '<div class="stock stock-low">
                                        🟡 Stok Menipis (' .
                                        $product['stock'] .
                                        ')
                                      </div>';

                            } else {

                                echo '<div class="stock stock-available">
                                        🟢 Tersedia (' .
                                        $product['stock'] .
                                        ')
                                      </div>';

                            }

                            ?>


                            <!-- DETAIL -->

                            <a
                                href="detail-produk.php?id=<?php echo $product['id']; ?>"
                                class="btn product-button"
                            >
                                Lihat Detail
                            </a>


                        </div>

                    </div>


                <?php endwhile; ?>


            <?php else: ?>


                <div class="no-product">

                    <h3>
                        Produk tidak ditemukan
                    </h3>

                    <p>
                        Coba gunakan kata pencarian lain.
                    </p>

                </div>


            <?php endif; ?>


        </div>

    </div>

</section>


<?php include "includes/footer.php"; ?>