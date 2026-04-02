<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($name && $email && $password && create_user($name, $email, $password)) {
        flash_message('Registration successful. You can now log in.');
        header('Location: login.php');
        exit;
    }

    flash_message('Registration failed. Import schema.sql and verify your database connection settings.', 'error');
}

$pageTitle = 'CampusCart | Register';
require_once __DIR__ . '/includes/header.php';
?>
<main class="section">
    <div class="container auth-grid">
        <section class="form-card reveal">
            <p class="eyebrow">Create Account</p>
            <h1>Register a new customer profile</h1>
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
                    Password
                    <input type="password" name="password" required>
                </label>
                <button class="button button--primary button--full" type="submit">Register</button>
            </form>
        </section>

        <section class="side-card reveal">
            <h3>Project Note</h3>
            <p>This page stores users in the MySQL <code>users</code> table using PHP password hashing.</p>
            <a class="button button--ghost button--full" href="schema.sql">Open SQL Schema</a>
        </section>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
