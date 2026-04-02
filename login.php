<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email && $password && login_user($email, $password)) {
        flash_message('Login successful.');
        header('Location: index.php');
        exit;
    }

    flash_message('Login failed. Ensure the database is connected and the account exists.', 'error');
}

$pageTitle = 'CampusCart | Login';
require_once __DIR__ . '/includes/header.php';
?>
<main class="section">
    <div class="container auth-grid">
        <section class="form-card reveal">
            <p class="eyebrow">Account Access</p>
            <h1>Login to your account</h1>
            <form class="checkout-form" method="post">
                <label>
                    Email Address
                    <input type="email" name="email" required>
                </label>
                <label>
                    Password
                    <input type="password" name="password" required>
                </label>
                <button class="button button--primary button--full" type="submit">Login</button>
            </form>
        </section>

        <section class="side-card reveal">
            <h3>New Here?</h3>
            <p>Create an account to connect the website with the SQL users table.</p>
            <a class="button button--ghost button--full" href="register.php">Register</a>
        </section>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
