
<?php
ini_set('display_errors', '0');
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.html');
    exit;
}

// Recibir datos del formulario
$nombre = trim($_POST['fname'] ?? '');
$apellido = trim($_POST['lname'] ?? '');
$telefono = trim($_POST['mphone'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['psw'] ?? '';
$confirmar = $_POST['psw_repeat'] ?? '';

// Validar campos obligatorios
if (
    $nombre === '' || $apellido === '' ||
    $telefono === '' || $email === '' ||
    $password === '' || $confirmar === ''
) {
    exit('Todos los campos son obligatorios.');
}

// Validar correo
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit('El correo electrónico no es válido.');
}

// Validar confirmación de contraseña
if ($password !== $confirmar) {
    exit('Las contraseñas no coinciden.');
}

// Conectar con Supabase
require __DIR__ . '/config/supabase.php';

if (!$supa_conn) {
    error_log('No se pudo conectar con Supabase.');
    exit('No fue posible conectar con la base de datos.');
}

// Comprobar si el correo o teléfono ya existen
$sql = 'SELECT email, mobile_phone
        FROM public.users
        WHERE LOWER(email) = LOWER($1)
           OR mobile_phone = $2
        LIMIT 1';

$result = pg_query_params(
    $supa_conn,
    $sql,
    [$email, $telefono]
);

if (!$result) {
    error_log('Error verificando duplicados: ' .
        pg_last_error($supa_conn));

    pg_close($supa_conn);
    exit('No se pudieron verificar los datos.');
}

$usuario_existente = pg_fetch_assoc($result);

// Identificar qué dato está repetido
if ($usuario_existente) {
    pg_close($supa_conn);

    echo "<script>
        alert('User email or mobile phone already exists');
        window.location.href = 'register.html';
    </script>";

    exit;
}

// Mantener compatibilidad con el login actual
$hash = md5($password);

// Registrar usuario
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
    exit('No se pudo registrar el usuario. Verifica los datos.');
}

pg_close($supa_conn);

// Registro exitoso
header('Location: login.html?registro=ok');
exit;
?>
