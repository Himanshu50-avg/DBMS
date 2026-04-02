<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $productId = (int) ($_POST['product_id'] ?? 0);
    if ($productId > 0) {
        add_to_cart($productId);
        flash_message('Product added to cart.');
    }
    header('Location: products.php');
    exit;
}

$search = trim($_GET['q'] ?? '');
$products = get_products($search ?: null);
$pageTitle = 'CampusCart | Products';
require_once __DIR__ . '/includes/header.php';
?>
<main class="section">
    <div class="container">
        <div class="catalog-header reveal">
            <div>
                <p class="eyebrow">Product Catalog</p>
                <h1>Browse products with real-time style search</h1>
            </div>
            <form class="search-form" method="get">
                <input type="search" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search products, category, or keyword">
                <button class="button button--primary" type="submit">Search</button>
            </form>
        </div>

        <div class="product-grid">
            <?php if (!$products): ?>
                <div class="empty-state reveal">
                    <h3>No products found</h3>
                    <p>Try a different search term to explore the catalog.</p>
                </div>
            <?php endif; ?>

            <?php foreach ($products as $product): ?>
                <article class="product-card reveal">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <div class="product-card__body">
                        <p class="product-category"><?php echo htmlspecialchars($product['category']); ?></p>
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                        <div class="product-meta">
                            <strong><?php echo format_price((float) $product['price']); ?></strong>
                            <span>Available: <?php echo (int) $product['stock']; ?></span>
                        </div>
                        <form method="post">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?php echo (int) $product['id']; ?>">
                            <button class="button button--primary" type="submit">Add to Cart</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
