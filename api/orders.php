<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');

$email = trim($_GET['email'] ?? '');

if ($email === '' && isset($_SESSION['user']['email'])) {
    $email = $_SESSION['user']['email'];
}

if ($email === '') {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Email is required to load orders.',
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'database' => db_available(),
    'orders' => get_orders_by_email($email),
]);
