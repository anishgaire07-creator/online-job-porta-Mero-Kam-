<?php

declare(strict_types=1);

namespace MeroKam\Helpers;

final class Upload
{
    private const MAX_BYTES = 5 * 1024 * 1024;
    private const ALLOWED_MIME = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    /** @return array{ok:bool,path?:string,error?:string} */
    public static function cv(array $file, string $subdir = 'cvs'): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['ok' => true, 'path' => null];
        }
        if (($file['error'] ?? 0) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'Upload failed'];
        }
        if (($file['size'] ?? 0) > self::MAX_BYTES) {
            return ['ok' => false, 'error' => 'File too large (max 5MB)'];
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            return ['ok' => false, 'error' => 'Only PDF or Word documents allowed'];
        }
        $ext = match ($mime) {
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            default => 'docx',
        };
        $base = dirname(__DIR__) . '/uploads/' . $subdir;
        if (!is_dir($base)) {
            mkdir($base, 0755, true);
        }
        $name = bin2hex(random_bytes(16)) . '.' . $ext;
        $dest = $base . '/' . $name;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return ['ok' => false, 'error' => 'Could not save file'];
        }
        $rel = 'uploads/' . $subdir . '/' . $name;
        return ['ok' => true, 'path' => $rel];
    }

    /** @return array{ok:bool,path?:string,error?:string} */
    public static function image(array $file, string $subdir = 'photos'): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['ok' => true, 'path' => null];
        }
        if (($file['error'] ?? 0) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'Upload failed'];
        }
        if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
            return ['ok' => false, 'error' => 'Image too large'];
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $allowed, true)) {
            return ['ok' => false, 'error' => 'Only JPG, PNG, WebP'];
        }
        $ext = $mime === 'image/png' ? 'png' : ($mime === 'image/webp' ? 'webp' : 'jpg');
        $base = dirname(__DIR__) . '/uploads/' . $subdir;
        if (!is_dir($base)) {
            mkdir($base, 0755, true);
        }
        $name = bin2hex(random_bytes(12)) . '.' . $ext;
        $dest = $base . '/' . $name;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return ['ok' => false, 'error' => 'Could not save image'];
        }
        return ['ok' => true, 'path' => 'uploads/' . $subdir . '/' . $name];
    }
}
