<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $productId = (int) ($_POST['product_id'] ?? 0);

    if ($action === 'add' && $productId > 0) {
        add_to_cart($productId);
        flash_message('Product added to cart.');
    }

    if ($action === 'update' && $productId > 0) {
        $quantity = (int) ($_POST['quantity'] ?? 1);
        update_cart_quantity($productId, $quantity);
        flash_message('Cart updated.');
    }

    if ($action === 'remove' && $productId > 0) {
        update_cart_quantity($productId, 0);
        flash_message('Item removed from cart.', 'info');
    }

    header('Location: cart.php');
    exit;
}

$items = cart_items();
$pageTitle = 'CampusCart | Cart';
require_once __DIR__ . '/includes/header.php';
?>
<main class="section">
    <div class="container">
        <div class="section-heading reveal">
            <p class="eyebrow">Shopping Cart</p>
            <h1>Review selected products before checkout</h1>
        </div>

        <?php if (!$items): ?>
            <div class="empty-state reveal">
                <h3>Your cart is empty</h3>
                <p>Add products from the catalog to continue shopping.</p>
                <a class="button button--primary" href="products.php">Browse Products</a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <div class="cart-table reveal">
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                        <p><?php echo htmlspecialchars($item['category']); ?></p>
                                    </td>
                                    <td><?php echo format_price((float) $item['price']); ?></td>
                                    <td>
                                        <form class="inline-form" method="post">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="product_id" value="<?php echo (int) $item['id']; ?>">
                                            <input class="qty-input" type="number" name="quantity" min="1" value="<?php echo (int) $item['quantity']; ?>">
                                            <button class="button button--small" type="submit">Save</button>
                                        </form>
                                    </td>
                                    <td><?php echo format_price((float) $item['line_total']); ?></td>
                                    <td>
                                        <form method="post">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="product_id" value="<?php echo (int) $item['id']; ?>">
                                            <button class="button button--ghost button--small" type="submit">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <aside class="summary-card reveal">
                    <h3>Order Summary</h3>
                    <div class="summary-row">
                        <span>Items</span>
                        <strong><?php echo cart_count(); ?></strong>
                    </div>
                    <div class="summary-row">
                        <span>Total</span>
                        <strong><?php echo format_price(cart_total()); ?></strong>
                    </div>
                    <a class="button button--primary button--full" href="checkout.php">Proceed to Checkout</a>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
