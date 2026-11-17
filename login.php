<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.html');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['psw'] ?? '';

if ($email === '' || $password === '' ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: login.html?error=credenciales');
    exit;
}

require __DIR__ . '/config/supabase.php';

if (!$supa_conn) {
    header('Location: login.html?error=conexion');
    exit;
}

$sql = 'SELECT id, firstname, lastname, email, password, photo_url
        FROM public.users
        WHERE LOWER(email) = LOWER($1)
        LIMIT 1';

$result = pg_query_params($supa_conn, $sql, [$email]);

if (!$result) {
    error_log(pg_last_error($supa_conn));
    pg_close($supa_conn);
    header('Location: login.html?error=conexion');
    exit;
}

$user = pg_fetch_assoc($result);

if (!$user) {
    pg_close($supa_conn);
    header('Location: login.html?error=credenciales');
    exit;
}

$storedPassword = $user['password'];
$validPassword = password_verify($password, $storedPassword);

// Compatibilidad con usuarios registrados anteriormente usando MD5.
if (!$validPassword && preg_match('/^[a-f0-9]{32}$/i', $storedPassword)) {
    $validPassword = hash_equals(strtolower($storedPassword), md5($password));

    if ($validPassword) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);

        pg_query_params(
            $supa_conn,
            'UPDATE public.users SET password = $1 WHERE id = $2',
            [$newHash, $user['id']]
        );
    }
}

if (!$validPassword) {
    pg_close($supa_conn);
    header('Location: login.html?error=credenciales');
    exit;
}

pg_close($supa_conn);

session_regenerate_id(true);

$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = trim($user['firstname'] . ' ' . $user['lastname']);
$_SESSION['photo_url'] = $user['photo_url'] ?? '';

header('Location: index.html');
exit;
