<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.html');
    exit;
}

$nombre = trim($_POST['fname'] ?? '');
$apellido = trim($_POST['lname'] ?? '');
$telefono = trim($_POST['mphone'] ?? '');
$email = trim($_POST['email'] ?? '');
$direccion = trim($_POST['address'] ?? '');

if (
    $nombre === '' || $apellido === '' || $telefono === '' ||
    $email === '' || $direccion === '' ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    exit('Verifica los campos obligatorios y el correo electrónico.');
}

require __DIR__ . '/config/supabase.php';
require __DIR__ . '/config/user_photo.php';

if (!$supa_conn) {
    exit('No se pudo conectar con la base de datos.');
}

$id = $_SESSION['user_id'];

$result = pg_query_params(
    $supa_conn,
    'SELECT photo_url FROM public.users WHERE id = $1',
    [$id]
);

$current = $result ? pg_fetch_assoc($result) : false;

if (!$current) {
    pg_close($supa_conn);
    exit('No se encontró el usuario.');
}

$duplicate = pg_query_params(
    $supa_conn,
    'SELECT id FROM public.users
     WHERE (LOWER(email) = LOWER($1) OR mobile_phone = $2)
       AND id <> $3
     LIMIT 1',
    [$email, $telefono, $id]
);

if (!$duplicate || pg_fetch_assoc($duplicate)) {
    pg_close($supa_conn);
    exit('El correo o teléfono ya pertenece a otro usuario.');
}

$oldPhoto = $current['photo_url'] ?? '';
$newPhoto = null;
$photoUrl = $oldPhoto ?: null;

try {
    if (
        isset($_FILES['photo']) &&
        $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE
    ) {
        $newPhoto = save_user_photo($_FILES['photo']);
        $photoUrl = $newPhoto;
    }

    $sql = 'UPDATE public.users
            SET firstname = $1,
                lastname = $2,
                mobile_phone = $3,
                email = $4,
                address = $5,
                photo_url = $6,
                update_at = NOW()
            WHERE id = $7';

    $updated = pg_query_params($supa_conn, $sql, [
        $nombre,
        $apellido,
        $telefono,
        $email,
        $direccion,
        $photoUrl,
        $id
    ]);

    if (!$updated) {
        throw new RuntimeException('No se pudieron guardar los cambios.');
    }

    pg_close($supa_conn);

    $_SESSION['user_name'] = $nombre . ' ' . $apellido;
    $_SESSION['photo_url'] = $photoUrl ?? '';

    // Eliminar la foto anterior únicamente si estaba en nuestra carpeta local.
    if ($newPhoto && $oldPhoto &&
        strpos($oldPhoto, 'uploads/users/') === 0 &&
        is_file(__DIR__ . '/' . $oldPhoto)) {
        unlink(__DIR__ . '/' . $oldPhoto);
    }

    header('Location: edit_user.php?actualizado=ok');
    exit;

} catch (Throwable $e) {
    if ($newPhoto && is_file(__DIR__ . '/' . $newPhoto)) {
        unlink(__DIR__ . '/' . $newPhoto);
    }

    error_log('Error actualizando usuario: ' . $e->getMessage());
    pg_close($supa_conn);
    http_response_code(400);
    exit($e->getMessage());
}
