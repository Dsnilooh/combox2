<?php

function save_user_photo(array $file): string
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Debes seleccionar una fotografía válida.');
    }

    if ($file['size'] > 3 * 1024 * 1024) {
        throw new RuntimeException('La fotografía no puede superar 3 MB.');
    }

    $imageInfo = @getimagesize($file['tmp_name']);

    if ($imageInfo === false) {
        throw new RuntimeException('El archivo no es una imagen válida.');
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    $mime = $imageInfo['mime'] ?? '';

    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Solo se permiten imágenes JPG, PNG o WEBP.');
    }

    $directory = dirname(__DIR__) . '/uploads/users';

    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
        throw new RuntimeException('No se pudo crear la carpeta de fotografías.');
    }

    $filename = 'user_' . bin2hex(random_bytes(12)) . '.' . $allowed[$mime];

    if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
        throw new RuntimeException('No se pudo guardar la fotografía.');
    }

    return 'uploads/users/' . $filename;
}
