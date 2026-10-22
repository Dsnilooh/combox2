<?php
ini_set('display_errors', '0');
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.html');
    exit;
}

$nombre = trim($_POST['fname'] ?? '');
$apellido = trim($_POST['lname'] ?? '');
$telefono = trim($_POST['mphone'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['psw'] ?? '';
$confirmar = $_POST['psw_repeat'] ?? '';

if (
    $nombre === '' || $apellido === '' ||
    $telefono === '' || $email === '' ||
    $password === '' || $confirmar === ''
) {
    exit('Todos los campos son obligatorios.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit('El correo electrónico no es válido.');
}

if ($password !== $confirmar) {
    exit('Las contraseñas no coinciden.');
}

require __DIR__ . '/config/supabase.php';

if (!$supa_conn) {
    error_log('No se pudo conectar con Supabase. Revisa config/supabase.php y el registro de errores de PHP.');
    exit('No fue posible conectar con Supabase. Revisa la configuración de conexión.');
}

$hash = md5($password);

$sql = 'INSERT INTO public.users
        (firstname, lastname, mobile_phone, email, password)
        VALUES ($1, $2, $3, $4, $5)';

$result = pg_query_params(
    $supa_conn,
    $sql,
    [$nombre, $apellido, $telefono, $email, $hash]
);

if (!$result) {
    error_log('Error de registro en Supabase: ' .
        pg_last_error($supa_conn));

    pg_close($supa_conn);
    exit('No se pudo registrar. Comprueba si el correo o teléfono ya existe.');
}

pg_close($supa_conn);

header('Location: login.html?registro=ok');
exit;
?>