<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.html');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['psw'] ?? '';

if (
    $email === '' || $password === '' ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    header('Location: login.html?error=credenciales');
    exit;
}

require __DIR__ . '/config/supabase.php';

if (!$supa_conn) {
    header('Location: login.html?error=conexion');
    exit;
}

$sql = 'SELECT id, firstname, email, password
        FROM public.users
        WHERE email = $1
        LIMIT 1';

$result = pg_query_params($supa_conn, $sql, [$email]);

if (!$result) {
    error_log('Error consultando Supabase: ' .
        pg_last_error($supa_conn));

    pg_close($supa_conn);
    header('Location: login.html?error=conexion');
    exit;
}

$user = pg_fetch_assoc($result);
pg_close($supa_conn);

if (
    !$user ||
    !hash_equals(strtolower($user['password']), md5($password))
) {
    header('Location: login.html?error=credenciales');
    exit;
}

session_regenerate_id(true);

$_SESSION['user_id'] = $user['id'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_name'] = $user['firstname'];

header('Location: index.html');
exit;
?>