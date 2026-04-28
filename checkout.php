<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer = [
        'name' => trim($_POST['name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'payment_method' => trim($_POST['payment_method'] ?? 'Cash on Delivery'),
    ];

    if ($customer['name'] && $customer['email'] && $customer['phone'] && $customer['address']) {
        if (place_order($customer)) {
            flash_message('Order placed successfully.');
            header('Location: index.php');
            exit;
        }

        flash_message('Database not connected yet. Import schema.sql into MySQL and update config.php.', 'error');
    } else {
        flash_message('Please complete all checkout fields.', 'error');
    }
}

$pageTitle = 'CampusCart | Checkout';
require_once __DIR__ . '/includes/header.php';
?>
<main class="section">
    <div class="container checkout-layout">
        <section class="form-card reveal">
            <p class="eyebrow">Checkout</p>
            <h1>Place your order</h1>
            <form class="checkout-form" method="post">
                <label>
                    Full Name
                    <input type="text" name="name" required>
                </label>
                <label>
                    Email Address
                    <input type="email" name="email" required>
                </label>
                <label>
                    Phone Number
                    <input type="tel" name="phone" required>
                </label>
                <label>
                    Delivery Address
                    <textarea name="address" rows="5" required></textarea>
                </label>
                <label>
                    Payment Method
                    <select name="payment_method">
                        <option>Cash on Delivery</option>
                        <option>UPI</option>
                        <option>Credit Card</option>
                    </select>
                </label>
                <button class="button button--primary button--full" type="submit">Confirm Order</button>
            </form>
        </section>

        <aside class="summary-card reveal">
            <h3>Checkout Summary</h3>
            <div class="summary-row">
                <span>Cart Items</span>
                <strong><?php echo cart_count(); ?></strong>
            </div>
            <div class="summary-row">
                <span>Payable Total</span>
                <strong><?php echo format_price(cart_total()); ?></strong>
            </div>
            <p class="summary-note">Import the SQL file and update database credentials to enable order insertion.</p>
        </aside>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
