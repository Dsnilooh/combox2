
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
$direccion = trim($_POST['address'] ?? '');
if ($direccion === '') {
    $direccion = 'No especificada';
}
$password = $_POST['psw'] ?? '';
$confirmar = $_POST['psw_repeat'] ?? '';

if (
    $nombre === '' || $apellido === '' || $telefono === '' ||
    $email === '' || $password === '' ||
    $confirmar === ''
) {
    exit('Todos los campos son obligatorios.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit('El correo electrónico no es válido.');
}

if ($password !== $confirmar) {
    exit('Las contraseñas no coinciden.');
}

if (strlen($password) < 8) {
    exit('La contraseña debe tener al menos 8 caracteres.');
}

require __DIR__ . '/config/supabase.php';
require __DIR__ . '/config/user_photo.php';

if (!$supa_conn) {
    exit('No fue posible conectar con la base de datos.');
}

$sql = 'SELECT id FROM public.users
        WHERE LOWER(email) = LOWER($1) OR mobile_phone = $2
        LIMIT 1';

$result = pg_query_params($supa_conn, $sql, [$email, $telefono]);

if (!$result) {
    error_log(pg_last_error($supa_conn));
    pg_close($supa_conn);
    exit('No se pudieron verificar los datos.');
}

if (pg_fetch_assoc($result)) {
    pg_close($supa_conn);
    exit('El correo electrónico o teléfono ya está registrado.');
}

$photoUrl = null;

try {
    $photoUrl = null;

    if (isset($_FILES['photo']) &&
        $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $photoUrl = save_user_photo($_FILES['photo']);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = 'INSERT INTO public.users
            (firstname, lastname, mobile_phone, email, password, address, photo_url)
            VALUES ($1, $2, $3, $4, $5, $6, $7)';

    $result = pg_query_params($supa_conn, $sql, [
        $nombre,
        $apellido,
        $telefono,
        $email,
        $hash,
        $direccion,
        $photoUrl
    ]);

    if (!$result) {
        throw new RuntimeException('No se pudo registrar el usuario. Verifica los datos.');
    }

    pg_close($supa_conn);
    header('Location: login.html?registro=ok');
    exit;

} catch (Throwable $e) {
    if ($photoUrl && is_file(__DIR__ . '/' . $photoUrl)) {
        unlink(__DIR__ . '/' . $photoUrl);
    }

    error_log('Error en registro: ' . $e->getMessage());
    pg_close($supa_conn);
    http_response_code(400);
    exit($e->getMessage());
}
