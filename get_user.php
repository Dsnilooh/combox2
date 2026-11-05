
<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_name'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No authenticated user']);
    exit;
}

echo json_encode([
    'name' => $_SESSION['user_name']
]);
?>
