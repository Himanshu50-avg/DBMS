<?php
require_once __DIR__ . '/../db.php';
$pageTitle = $pageTitle ?? 'CampusCart';
$flash = flash_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="CampusCart is a clean PHP and MySQL e-commerce project for academic submission.">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="site-header">
        <div class="container nav-shell">
            <a class="brand" href="index.php">CampusCart</a>
            <button class="nav-toggle" id="navToggle" aria-label="Open navigation" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <nav class="nav-links" id="navMenu">
                <a href="index.php">Home</a>
                <a href="products.php">Products</a>
                <a href="cart.php">Cart <span class="badge"><?php echo cart_count(); ?></span></a>
                <a href="checkout.php">Checkout</a>
                <a href="login.php"><?php echo isset($_SESSION['user']) ? 'Account' : 'Login'; ?></a>
                <button class="theme-toggle" id="themeToggle" type="button">Theme</button>
            </nav>
        </div>
    </header>

    <?php if ($flash): ?>
        <div class="container">
            <div class="flash flash--<?php echo htmlspecialchars($flash['type']); ?>">
                <?php echo htmlspecialchars($flash['text']); ?>
            </div>
        </div>
    <?php endif; ?>
