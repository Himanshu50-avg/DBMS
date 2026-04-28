<?php
require_once __DIR__ . '/config.php';

function sample_products(): array
{
    return [
        ['id' => 1, 'name' => 'AeroFit Smart Watch', 'description' => 'Fitness-focused smart watch with heart-rate tracking and AMOLED display.', 'price' => 6999, 'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=900&q=80', 'category' => 'Wearables', 'stock' => 12],
        ['id' => 2, 'name' => 'NovaSound Headphones', 'description' => 'Wireless over-ear headphones with noise cancellation and 30-hour battery life.', 'price' => 4599, 'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=900&q=80', 'category' => 'Audio', 'stock' => 18],
        ['id' => 3, 'name' => 'PixelNest Speaker', 'description' => 'Compact Bluetooth speaker with crisp bass and modern fabric finish.', 'price' => 2499, 'image' => 'https://images.unsplash.com/photo-1545454675-3531b543be5d?auto=format&fit=crop&w=900&q=80', 'category' => 'Audio', 'stock' => 26],
        ['id' => 4, 'name' => 'LumaDesk Lamp', 'description' => 'Minimal LED study lamp with touch brightness controls and USB charging.', 'price' => 1799, 'image' => 'https://images.unsplash.com/photo-1517999144091-3d9dca6d1e43?auto=format&fit=crop&w=900&q=80', 'category' => 'Home', 'stock' => 30],
        ['id' => 5, 'name' => 'TerraBottle Pro', 'description' => 'Insulated stainless bottle designed for college, travel, and daily hydration.', 'price' => 899, 'image' => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?auto=format&fit=crop&w=900&q=80', 'category' => 'Lifestyle', 'stock' => 40],
        ['id' => 6, 'name' => 'CodePack Backpack', 'description' => 'Laptop backpack with anti-theft zip design and ergonomic storage layout.', 'price' => 3299, 'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=900&q=80', 'category' => 'Accessories', 'stock' => 14],
    ];
}

function format_price(float $price): string
{
    return 'Rs. ' . number_format($price, 2);
}

function db_available(): bool
{
    return db() instanceof PDO;
}

function get_products(?string $search = null): array
{
    $pdo = db();
    if ($pdo instanceof PDO) {
        $sql = '
            SELECT
                p.product_id AS id,
                p.product_name AS name,
                p.description,
                p.price,
                p.image_url AS image,
                c.category_name AS category,
                p.stock
            FROM products p
            LEFT JOIN categories c ON c.category_id = p.category_id
        ';
        $params = [];
        if ($search) {
            $sql .= ' WHERE p.product_name LIKE :search OR p.description LIKE :search OR c.category_name LIKE :search ';
            $params['search'] = '%' . $search . '%';
        }
        $sql .= ' ORDER BY p.created_at DESC';
        $statement = $pdo->prepare($sql);
        $statement->execute($params);
        $rows = $statement->fetchAll();
        if ($rows) {
            return $rows;
        }
    }

    $products = sample_products();
    if (!$search) {
        return $products;
    }

    $search = strtolower($search);
    return array_values(array_filter($products, function (array $product) use ($search): bool {
        return str_contains(strtolower($product['name']), $search)
            || str_contains(strtolower($product['description']), $search)
            || str_contains(strtolower($product['category']), $search);
    }));
}

function get_product_by_id(int $productId): ?array
{
    foreach (get_products() as $product) {
        if ((int) $product['id'] === $productId) {
            return $product;
        }
    }

    return null;
}

function featured_products(int $limit = 4): array
{
    return array_slice(get_products(), 0, $limit);
}

function cart_items(): array
{
    $items = $_SESSION['cart'] ?? [];
    $cart = [];

    foreach ($items as $productId => $quantity) {
        $product = get_product_by_id((int) $productId);
        if (!$product) {
            continue;
        }

        $product['quantity'] = (int) $quantity;
        $product['line_total'] = ((float) $product['price']) * $product['quantity'];
        $cart[] = $product;
    }

    return $cart;
}

function cart_count(): int
{
    return array_sum($_SESSION['cart'] ?? []);
}

function cart_total(): float
{
    $total = 0;
    foreach (cart_items() as $item) {
        $total += (float) $item['line_total'];
    }

    return $total;
}

function add_to_cart(int $productId, int $quantity = 1): void
{
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + max(1, $quantity);
}

function update_cart_quantity(int $productId, int $quantity): void
{
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$productId]);
        return;
    }

    $_SESSION['cart'][$productId] = $quantity;
}

function create_user(string $name, string $email, string $password): array
{
    $pdo = db();
    if (!$pdo instanceof PDO) {
        return ['success' => false, 'message' => 'Database connection not available.'];
    }

    try {
        $statement = $pdo->prepare(
            'INSERT INTO users (full_name, email, password_hash, created_at) VALUES (:name, :email, :password_hash, NOW())'
        );
        $statement->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
        return ['success' => true, 'message' => 'Registration successful.'];
    } catch (Throwable $throwable) {
        return ['success' => false, 'message' => 'Registration failed. This email may already exist.'];
    }
}

function login_user(string $email, string $password): array
{
    $pdo = db();
    if (!$pdo instanceof PDO) {
        return ['success' => false, 'message' => 'Database connection not available.'];
    }

    $statement = $pdo->prepare('SELECT user_id, full_name, email, password_hash FROM users WHERE email = :email LIMIT 1');
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }

    $_SESSION['user'] = [
        'id' => $user['user_id'],
        'name' => $user['full_name'],
        'email' => $user['email'],
    ];

    return ['success' => true, 'message' => 'Login successful.', 'user' => $_SESSION['user']];
}

function admin_login(string $email, string $password): bool
{
    if ($email === ADMIN_EMAIL && $password === ADMIN_PASSWORD) {
        $_SESSION['admin'] = ['email' => $email];
        return true;
    }
    return false;
}

function is_admin_logged_in(): bool
{
    return isset($_SESSION['admin']);
}

function admin_logout(): void
{
    unset($_SESSION['admin']);
}

function find_or_create_user(string $name, string $email, string $phone): ?int
{
    $pdo = db();
    if (!$pdo instanceof PDO) {
        return null;
    }

    $existing = $pdo->prepare('SELECT user_id FROM users WHERE email = :email LIMIT 1');
    $existing->execute(['email' => $email]);
    $row = $existing->fetch();
    if ($row) {
        $update = $pdo->prepare('UPDATE users SET full_name = :name, phone = :phone WHERE user_id = :user_id');
        $update->execute(['name' => $name, 'phone' => $phone, 'user_id' => $row['user_id']]);
        return (int) $row['user_id'];
    }

    $statement = $pdo->prepare(
        'INSERT INTO users (full_name, email, password_hash, phone, created_at) VALUES (:name, :email, :password_hash, :phone, NOW())'
    );
    $statement->execute([
        'name' => $name,
        'email' => $email,
        'password_hash' => password_hash('guest-checkout', PASSWORD_DEFAULT),
        'phone' => $phone,
    ]);

    return (int) $pdo->lastInsertId();
}

function place_order_from_items(array $customer, array $items): array
{
    if (!$items) {
        return ['success' => false, 'message' => 'Your cart is empty.'];
    }

    $pdo = db();
    if (!$pdo instanceof PDO) {
        return ['success' => false, 'message' => 'Database connection not available.'];
    }

    try {
        $pdo->beginTransaction();
        $userId = isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : find_or_create_user($customer['name'], $customer['email'], $customer['phone']);
        if (!$userId) {
            throw new RuntimeException('Unable to resolve customer.');
        }

        $totalAmount = 0;
        foreach ($items as $item) {
            $totalAmount += ((float) $item['price']) * ((int) $item['quantity']);
        }

        $orderStatement = $pdo->prepare(
            'INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, shipping_address, order_date, order_status, total_amount) VALUES (:user_id, :customer_name, :customer_email, :customer_phone, :shipping_address, NOW(), :order_status, :total_amount)'
        );
        $orderStatement->execute([
            'user_id' => $userId,
            'customer_name' => $customer['name'],
            'customer_email' => $customer['email'],
            'customer_phone' => $customer['phone'],
            'shipping_address' => $customer['address'],
            'order_status' => 'Processing',
            'total_amount' => $totalAmount,
        ]);
        $orderId = (int) $pdo->lastInsertId();

        $itemStatement = $pdo->prepare(
            'INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (:order_id, :product_id, :quantity, :unit_price)'
        );
        foreach ($items as $item) {
            $itemStatement->execute([
                'order_id' => $orderId,
                'product_id' => (int) $item['id'],
                'quantity' => (int) $item['quantity'],
                'unit_price' => (float) $item['price'],
            ]);
        }

        $paymentStatement = $pdo->prepare(
            'INSERT INTO payments (order_id, payment_method, payment_status, paid_at) VALUES (:order_id, :payment_method, :payment_status, NOW())'
        );
        $paymentStatement->execute([
            'order_id' => $orderId,
            'payment_method' => $customer['payment_method'],
            'payment_status' => 'Completed',
        ]);

        $pdo->commit();
        return ['success' => true, 'message' => 'Order stored successfully in MySQL.', 'order_id' => $orderId];
    } catch (Throwable $throwable) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ['success' => false, 'message' => 'Order could not be stored. Check your database setup.'];
    }
}

function place_order(array $customer): bool
{
    $customerWithPhone = array_merge(
        [
            'phone' => trim($_POST['phone'] ?? '0000000000'),
        ],
        $customer
    );

    $result = place_order_from_items($customerWithPhone, cart_items());
    if ($result['success']) {
        $_SESSION['cart'] = [];
        return true;
    }

    return false;
}

function admin_stats(): array
{
    $pdo = db();
    if (!$pdo instanceof PDO) {
        return ['products' => 0, 'bookings' => 0, 'customers' => 0];
    }

    return [
        'products' => (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
        'bookings' => (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
        'customers' => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    ];
}

function admin_all_products(): array
{
    return get_products();
}

function admin_recent_orders(): array
{
    $pdo = db();
    if (!$pdo instanceof PDO) {
        return [];
    }

    $statement = $pdo->query('SELECT order_id, customer_name, customer_email, customer_phone, shipping_address, total_amount, order_status, order_date FROM orders ORDER BY order_date DESC LIMIT 8');
    return $statement->fetchAll();
}

function get_orders_by_email(string $email): array
{
    $pdo = db();
    if (!$pdo instanceof PDO) {
        return [];
    }

    $statement = $pdo->prepare(
        'SELECT order_id, customer_name, customer_email, customer_phone, shipping_address, total_amount, order_status, order_date
         FROM orders
         WHERE customer_email = :email
         ORDER BY order_date DESC'
    );
    $statement->execute(['email' => $email]);
    return $statement->fetchAll();
}

function ensure_category(string $categoryName): ?int
{
    $pdo = db();
    if (!$pdo instanceof PDO) {
        return null;
    }

    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $categoryName), '-'));
    $find = $pdo->prepare('SELECT category_id FROM categories WHERE category_name = :name LIMIT 1');
    $find->execute(['name' => $categoryName]);
    $row = $find->fetch();
    if ($row) {
        return (int) $row['category_id'];
    }

    $insert = $pdo->prepare('INSERT INTO categories (category_name, category_slug) VALUES (:name, :slug)');
    $insert->execute(['name' => $categoryName, 'slug' => $slug ?: uniqid('cat-')]);
    return (int) $pdo->lastInsertId();
}

function admin_add_product(array $data): array
{
    $pdo = db();
    if (!$pdo instanceof PDO) {
        return ['success' => false, 'message' => 'Database connection not available.'];
    }

    $categoryId = ensure_category($data['category']);
    if (!$categoryId) {
        return ['success' => false, 'message' => 'Unable to save category.'];
    }

    try {
        $statement = $pdo->prepare(
            'INSERT INTO products (category_id, product_name, description, price, stock, image_url, created_at) VALUES (:category_id, :product_name, :description, :price, :stock, :image_url, NOW())'
        );
        $statement->execute([
            'category_id' => $categoryId,
            'product_name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'stock' => $data['stock'],
            'image_url' => $data['image'],
        ]);
        return ['success' => true, 'message' => 'Product added successfully.'];
    } catch (Throwable $throwable) {
        return ['success' => false, 'message' => 'Failed to add product.'];
    }
}

function admin_delete_product(int $productId): array
{
    $pdo = db();
    if (!$pdo instanceof PDO) {
        return ['success' => false, 'message' => 'Database connection not available.'];
    }

    try {
        $statement = $pdo->prepare('DELETE FROM products WHERE product_id = :product_id');
        $statement->execute(['product_id' => $productId]);
        return ['success' => true, 'message' => 'Product deleted successfully.'];
    } catch (Throwable $throwable) {
        return ['success' => false, 'message' => 'Unable to delete product. Make sure it is not linked to orders.'];
    }
}
