<?php
$pageTitle = 'CampusCart | Home';
require_once __DIR__ . '/includes/header.php';
$featured = featured_products();
?>
<main>
    <section class="hero">
        <div class="container hero-grid">
            <div class="hero-copy reveal">
                <p class="eyebrow">SQL Powered E-Commerce</p>
                <h1>Professional online store project with a real database structure.</h1>
                <p class="hero-text">
                    CampusCart demonstrates a complete e-commerce flow for college submission, including searchable
                    products, shopping cart management, user accounts, and a checkout process backed by MySQL tables.
                </p>
                <div class="hero-actions">
                    <a class="button button--primary" href="products.php">Shop Products</a>
                    <a class="button button--ghost" href="schema.sql">View SQL Schema</a>
                </div>
            </div>
            <div class="hero-panel reveal">
                <div class="stat-card">
                    <span>Modules</span>
                    <strong>Catalog, Cart, Checkout, Authentication</strong>
                </div>
                <div class="stat-card">
                    <span>Database</span>
                    <strong>Users, Products, Categories, Orders, Payments, Reviews</strong>
                </div>
                <div class="stat-card">
                    <span>Optimization</span>
                    <strong>Indexed search fields and relational joins ready for scaling</strong>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-heading reveal">
                <p class="eyebrow">Featured Products</p>
                <h2>Popular products from the demo catalog</h2>
            </div>
            <div class="product-grid">
                <?php foreach ($featured as $product): ?>
                    <article class="product-card reveal">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="product-card__body">
                            <p class="product-category"><?php echo htmlspecialchars($product['category']); ?></p>
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p><?php echo htmlspecialchars($product['description']); ?></p>
                            <div class="product-meta">
                                <strong><?php echo format_price((float) $product['price']); ?></strong>
                                <span>Stock: <?php echo (int) $product['stock']; ?></span>
                            </div>
                            <form method="post" action="cart.php">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?php echo (int) $product['id']; ?>">
                                <button class="button button--primary" type="submit">Add to Cart</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section section--soft">
        <div class="container split-card reveal">
            <div>
                <p class="eyebrow">Why This Project Works</p>
                <h2>Submission-ready design with practical full-stack logic</h2>
            </div>
            <ul class="feature-list">
                <li>Responsive storefront built without frameworks</li>
                <li>PHP session-based cart with MySQL-ready order processing</li>
                <li>Search-capable product catalog with seed data fallback</li>
                <li>Clean UI that looks like a modern academic project demo</li>
            </ul>
        </div>
    </section>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
