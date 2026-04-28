<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$customer = [
    'name' => trim($data['name'] ?? ''),
    'email' => trim($data['email'] ?? ''),
    'phone' => trim($data['phone'] ?? ''),
    'address' => trim($data['address'] ?? ''),
    'payment_method' => trim($data['payment_method'] ?? 'Cash on Delivery'),
];
$items = $data['items'] ?? [];

if (!$customer['name'] || !$customer['email'] || !$customer['phone'] || !$customer['address']) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Please complete all checkout fields.']);
    exit;
}

if (!is_array($items) || !$items) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
    exit;
}

echo json_encode(place_order_from_items($customer, $items));
