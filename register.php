<?php
// Evitar mostrar avisos de conexión en la respuesta
ini_set('display_errors', '0');
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.html');
    exit;
}

// Recibir datos del formulario
$f_name = trim($_POST['fname'] ?? '');
$l_name = trim($_POST['lname'] ?? '');
$m_phone = trim($_POST['mphone'] ?? '');
$e_mail = trim($_POST['email'] ?? '');
$p_assword = $_POST['psw'] ?? '';
$p_assword_repeat = $_POST['psw_repeat'] ?? '';

// Validar datos
if (
    $f_name === '' ||
    $l_name === '' ||
    $m_phone === '' ||
    $e_mail === '' ||
    $p_assword === '' ||
    $p_assword_repeat === ''
) {
    exit('Todos los campos son obligatorios.');
}

if (!filter_var($e_mail, FILTER_VALIDATE_EMAIL)) {
    exit('El correo electrónico no es válido.');
}

if ($p_assword !== $p_assword_repeat) {
    exit('Las contraseñas no coinciden. Regresa al formulario e inténtalo de nuevo.');
}

// Mantener el método MD5 del proyecto anterior
$pass_enc = md5($p_assword);

// Conectar con ambas bases de datos
require __DIR__ . '/config/database.php';

// Comprobar las conexiones
if (!$local_conn || !$supa_conn) {
    exit('No fue posible conectar con las dos bases de datos.');
}

// Insertar en PostgreSQL local usando parámetros
$sql_local = '
    INSERT INTO public.users
        (firstname, lastname, mobile_phone, email, password)
    VALUES ($1, $2, $3, $4, $5)
    RETURNING id
';

$local_res = pg_query_params(
    $local_conn,
    $sql_local,
    [$f_name, $l_name, $m_phone, $e_mail, $pass_enc]
);

if (!$local_res) {
    exit('No se pudo registrar en PostgreSQL local. Verifica los datos o si el correo/teléfono ya existe.');
}

$local_user = pg_fetch_assoc($local_res);
$new_id = $local_user['id'];

// Insertar en Supabase conservando el mismo ID
$sql_supa = '
    INSERT INTO public.users
        (id, firstname, lastname, mobile_phone, email, password)
    VALUES ($1, $2, $3, $4, $5, $6)
';

$supa_res = pg_query_params(
    $supa_conn,
    $sql_supa,
    [$new_id, $f_name, $l_name, $m_phone, $e_mail, $pass_enc]
);

if (!$supa_res) 
    if (!$supa_res) {
    $error = pg_last_error($supa_conn);

    $rollback_sql = 'DELETE FROM public.users WHERE id = $1';
    pg_query_params($local_conn, $rollback_sql, [$new_id]);

    exit('Error de Supabase: ' . htmlspecialchars($error));
}

// Cerrar conexiones
pg_close($local_conn);
pg_close($supa_conn);

// Confirmar el registro
echo '<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Registro completado</title>
</head>
<body>
<h2>Usuario registrado correctamente en ambas bases de datos.</h2>
<a href="login.html">Ir al inicio de sesión</a>
</body>
</html>';

?>