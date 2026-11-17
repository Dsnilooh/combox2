<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No authenticated user']);
    exit;
}

require __DIR__ . '/config/supabase.php';

if (!$supa_conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$sql = 'SELECT id, firstname, lastname, photo_url
        FROM public.users
        WHERE id = $1 LIMIT 1';

$result = pg_query_params($supa_conn, $sql, [$_SESSION['user_id']]);

if (!$result || !($user = pg_fetch_assoc($result))) {
    pg_close($supa_conn);
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit;
}

$photoUrl = trim($user['photo_url'] ?? '');

// Convertir automáticamente las rutas antiguas de COMBOX.
if (preg_match('~^photos/([^/]+)$~', $photoUrl, $match)) {
    $newPath = 'uploads/users/' . basename($match[1]);

    if (is_file(__DIR__ . '/' . $newPath)) {
        $photoUrl = $newPath;

        pg_query_params(
            $supa_conn,
            'UPDATE public.users SET photo_url = $1 WHERE id = $2',
            [$photoUrl, $user['id']]
        );
    }
}

pg_close($supa_conn);

$_SESSION['user_name'] = trim($user['firstname'] . ' ' . $user['lastname']);
$_SESSION['photo_url'] = $photoUrl;

echo json_encode([
    'id' => $user['id'],
    'name' => $_SESSION['user_name'],
    'photo_url' => $photoUrl
]);