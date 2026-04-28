<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');

$search = isset($_GET['q']) ? trim($_GET['q']) : null;

echo json_encode([
    'success' => true,
    'database' => db_available(),
    'products' => get_products($search ?: null),
]);
