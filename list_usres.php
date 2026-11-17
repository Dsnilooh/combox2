<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

require __DIR__ . '/config/supabase.php';

if (!$supa_conn) {
    exit('No se pudo conectar con la base de datos.');
}

$sql = 'SELECT id, firstname, lastname, email, mobile_phone, photo_url
        FROM public.users
        ORDER BY id DESC';

$result = pg_query($supa_conn, $sql);

if (!$result) {
    error_log(pg_last_error($supa_conn));
    pg_close($supa_conn);
    exit('No se pudieron consultar los usuarios.');
}

$users = pg_fetch_all($result) ?: [];
pg_close($supa_conn);

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Usuarios - COMBOX2</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Usuarios registrados</h1>
        <a href="index.html" class="btn btn-secondary">Volver al inicio</a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Fotografía</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="text-center">
                                <img
                                    src="<?= e($user['photo_url'] ?: 'img/undraw_profile.svg') ?>"
                                    alt="Foto"
                                    onerror="this.src='img/undraw_profile.svg'"
                                    style="width:55px;height:55px;object-fit:cover;border-radius:50%;">
                            </td>
                            <td><?= e($user['firstname'] . ' ' . $user['lastname']) ?></td>
                            <td><?= e($user['email']) ?></td>
                            <td><?= e($user['mobile_phone']) ?></td>
                            <td>
                                <?php if ((string)$user['id'] === (string)$_SESSION['user_id']): ?>
                                    <a class="btn btn-primary btn-sm"
                                       href="edit_user.php">Editar mi perfil</a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$users): ?>
                        <tr><td colspan="5" class="text-center">No hay usuarios registrados.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>