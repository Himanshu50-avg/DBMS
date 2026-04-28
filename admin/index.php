<?php
require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        if (admin_login($email, $password)) {
            flash_message('Admin login successful.');
        } else {
            flash_message('Invalid admin credentials.', 'error');
        }
        header('Location: index.php');
        exit;
    }

    if ($action === 'logout') {
        admin_logout();
        flash_message('Admin logged out.', 'info');
        header('Location: index.php');
        exit;
    }

    if (!is_admin_logged_in()) {
        flash_message('Please log in as admin first.', 'error');
        header('Location: index.php');
        exit;
    }

    if ($action === 'add_product') {
        $result = admin_add_product([
            'name' => trim($_POST['name'] ?? ''),
            'category' => trim($_POST['category'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'price' => (float) ($_POST['price'] ?? 0),
            'stock' => (int) ($_POST['stock'] ?? 0),
            'image' => trim($_POST['image'] ?? ''),
        ]);
        flash_message($result['message'], $result['success'] ? 'success' : 'error');
        header('Location: index.php');
        exit;
    }

    if ($action === 'delete_product') {
        $result = admin_delete_product((int) ($_POST['product_id'] ?? 0));
        flash_message($result['message'], $result['success'] ? 'success' : 'error');
        header('Location: index.php');
        exit;
    }
}

$flash = flash_message();
$stats = admin_stats();
$products = admin_all_products();
$orders = admin_recent_orders();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusCart Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header class="site-header">
        <div class="container nav-shell">
            <a class="brand" href="index.php">CampusCart Admin</a>
            <nav class="nav-links">
                <a href="../index.html">Storefront</a>
                <?php if (is_admin_logged_in()): ?>
                    <form method="post">
                        <input type="hidden" name="action" value="logout">
                        <button class="theme-toggle" type="submit">Logout</button>
                    </form>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="section">
        <div class="container">
            <?php if ($flash): ?>
                <div class="flash flash--<?php echo htmlspecialchars($flash['type']); ?>">
                    <?php echo htmlspecialchars($flash['text']); ?>
                </div>
            <?php endif; ?>

            <?php if (!is_admin_logged_in()): ?>
                <section class="form-card admin-auth-card">
                    <p class="eyebrow">Admin Login</p>
                    <h2>Manage products and bookings</h2>
                    <form class="checkout-form" method="post">
                        <input type="hidden" name="action" value="login">
                        <label>
                            Admin Email
                            <input type="email" name="email" placeholder="admin@campuscart.com" required>
                        </label>
                        <label>
                            Password
                            <input type="password" name="password" placeholder="Enter admin password" required>
                        </label>
                        <button class="button button--primary button--full" type="submit">Login as Admin</button>
                    </form>
                    <p class="database-note database-note--warning">Default admin credentials are set in <code>config.php</code>.</p>
                </section>
            <?php else: ?>
                <section class="catalog-header">
                    <div>
                        <p class="eyebrow">Admin Dashboard</p>
                        <h2>Products, customer records, and booking counts</h2>
                    </div>
                </section>

                <section class="stats-grid">
                    <article class="summary-card">
                        <h3>Total Products</h3>
                        <strong class="metric-number"><?php echo $stats['products']; ?></strong>
                    </article>
                    <article class="summary-card">
                        <h3>Total Bookings</h3>
                        <strong class="metric-number"><?php echo $stats['bookings']; ?></strong>
                    </article>
                    <article class="summary-card">
                        <h3>Total Customers</h3>
                        <strong class="metric-number"><?php echo $stats['customers']; ?></strong>
                    </article>
                </section>

                <section class="stack-grid admin-sections">
                    <article class="form-card">
                        <p class="eyebrow">Add Product</p>
                        <h2>Add a new product to MySQL</h2>
                        <form class="checkout-form" method="post">
                            <input type="hidden" name="action" value="add_product">
                            <label>
                                Product Name
                                <input type="text" name="name" required>
                            </label>
                            <label>
                                Category
                                <input type="text" name="category" required>
                            </label>
                            <label>
                                Description
                                <textarea name="description" rows="4" required></textarea>
                            </label>
                            <label>
                                Price
                                <input type="number" name="price" min="0" step="0.01" required>
                            </label>
                            <label>
                                Stock
                                <input type="number" name="stock" min="0" required>
                            </label>
                            <label>
                                Image URL
                                <input type="url" name="image" required>
                            </label>
                            <button class="button button--primary button--full" type="submit">Add Product</button>
                        </form>
                    </article>

                    <article class="cart-table">
                        <div class="compact-heading">
                            <p class="eyebrow">Product List</p>
                            <h2>Delete products</h2>
                        </div>
                        <div class="data-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                                            <td><?php echo htmlspecialchars($product['category']); ?></td>
                                            <td><?php echo format_price((float) $product['price']); ?></td>
                                            <td><?php echo (int) $product['stock']; ?></td>
                                            <td>
                                                <form method="post">
                                                    <input type="hidden" name="action" value="delete_product">
                                                    <input type="hidden" name="product_id" value="<?php echo (int) $product['id']; ?>">
                                                    <button class="button button--ghost button--small" type="submit">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </article>
                </section>

                <section class="cart-table admin-orders">
                    <div class="compact-heading">
                        <p class="eyebrow">Recent Bookings</p>
                        <h2>Customer information stored from checkout</h2>
                    </div>
                    <div class="data-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$orders): ?>
                                    <tr>
                                        <td colspan="6">No bookings found yet.</td>
                                    </tr>
                                <?php endif; ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo (int) $order['order_id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong><br>
                                            <?php echo htmlspecialchars($order['customer_email']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($order['customer_phone']); ?></td>
                                        <td><?php echo htmlspecialchars($order['shipping_address']); ?></td>
                                        <td><?php echo format_price((float) $order['total_amount']); ?></td>
                                        <td><?php echo htmlspecialchars($order['order_status']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
