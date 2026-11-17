<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

require __DIR__ . '/config/supabase.php';

$result = pg_query_params(
    $supa_conn,
    'SELECT id, firstname, lastname, mobile_phone, email, address, photo_url
     FROM public.users WHERE id = $1 LIMIT 1',
    [$_SESSION['user_id']]
);

if (!$result || !($user = pg_fetch_assoc($result))) {
    pg_close($supa_conn);
    exit('No se encontró el usuario.');
}

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
    <title>Editar perfil - COMBOX2</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-primary">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow my-5">
                <div class="card-body p-5">
                    <h1 class="h4 text-center mb-4">Editar mi perfil</h1>

                    <div class="text-center mb-4">
                        <img
                            src="<?= e($user['photo_url'] ?: 'img/undraw_profile.svg') ?>"
                            alt="Foto actual"
                            onerror="this.src='img/undraw_profile.svg'"
                            style="width:120px;height:120px;object-fit:cover;border-radius:50%;">
                    </div>

                    <form action="update_user.php" method="POST"
                          enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input class="form-control" name="fname"
                                value="<?= e($user['firstname']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Apellido</label>
                            <input class="form-control" name="lname"
                                value="<?= e($user['lastname']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Teléfono</label>
                            <input class="form-control" name="mphone"
                                value="<?= e($user['mobile_phone']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Correo electrónico</label>
                            <input class="form-control" type="email" name="email"
                                value="<?= e($user['email']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Dirección</label>
                            <input class="form-control" name="address"
                                value="<?= e($user['address']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Cambiar fotografía (opcional)</label>
                            <input class="form-control" type="file" name="photo"
                                accept="image/jpeg,image/png,image/webp">
                            <small class="form-text text-muted">JPG, PNG o WEBP. Máximo 3 MB.</small>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            Guardar cambios
                        </button>
                        <a href="list_usres.php" class="btn btn-secondary btn-block">
                            Volver al listado
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
