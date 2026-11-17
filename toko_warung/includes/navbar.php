<?php

require_once __DIR__ . "/../config/cart.php";

$cartCount = getCartCount();

?>

<nav class="navbar">

    <div class="container navbar-content">

        <a href="index.php" class="logo">
            TOKO KITA
        </a>

        <div class="nav-menu">

            <a href="index.php">
                Beranda
            </a>

            <a href="produk.php">
                Produk
            </a>

            <a href="produk.php?category=4">
                Warung Kopi
            </a>

            <a href="#">
                Kontak
            </a>

           <a href="keranjang.php">
                🛒 Keranjang
           <?php if ($cartCount > 0): ?>
          (<?php echo $cartCount; ?>)
           <?php endif; ?>

</a>

        </div>

    </div>

</nav>