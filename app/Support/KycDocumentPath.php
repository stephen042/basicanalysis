<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Resolves KYC document paths saved by both the current private-storage
 * uploader and the previous public "uploads" uploader.
 *
 * New submissions are stored on the private "local" disk as
 * "kyc-documents/{user_id}/{filename}". Legacy submissions stored
 * "uploads/{filename}" on the public disk (and some older deployments wrote
 * absolute URLs or paths containing "storage/app/public").
 */
class KycDocumentPath
{
    /**
     * Normalise any supported stored value into a relative disk path.
     * Returns null for values that are not KYC uploads.
     */
    public static function normalize($storedPath): ?string
    {
        $path = str_replace('\\', '/', trim((string) $storedPath));
        if ($path === '') {
            return null;
        }

        // Full URLs (including APP_URL/storage/app/public/...) are reduced to
        // their path component before prefix stripping.
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $path = (string) parse_url($path, PHP_URL_PATH);
        }

        $path = rawurldecode($path);

        $patterns = [
            '~(?:^|/)storage/app/public/(.+)$~i',
            '~(?:^|/)storage/app/(.+)$~i',
            '~(?:^|/)public/storage/(.+)$~i',
            '~(?:^|/)public/(uploads/.+)$~i',
            '~(?:^|/)app/public/(.+)$~i',
            '~(?:^|/)public_html/storage/app/public/(.+)$~i',
            '~(?:^|/)public_html/(uploads/.+)$~i',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $path, $matches)) {
                $path = $matches[1];
                break;
            }
        }

        $path = ltrim($path, '/');
        foreach (['storage/app/public/', 'storage/app/', 'public/storage/', 'public/'] as $prefix) {
            if (strpos($path, $prefix) === 0) {
                $path = substr($path, strlen($prefix));
                break;
            }
        }

        if (strpos($path, 'uploads/') !== 0 && strpos($path, 'kyc-documents/') !== 0) {
            return null;
        }
        if (strpos($path, '..') !== false || strpos($path, "\0") !== false) {
            return null;
        }

        return $path;
    }

    /**
     * Locate a stored document and return the disk, relative path and absolute
     * path. Returns null when the file is not present on any known location.
     *
     * @return array{disk: string, path: string, absolute: string}|null
     */
    public static function resolve($storedPath): ?array
    {
        $path = static::normalize($storedPath);
        if (!$path) {
            return null;
        }

        $disks = strpos($path, 'kyc-documents/') === 0
            ? ['local', 'public']
            : ['public', 'local'];

        foreach ($disks as $disk) {
            try {
                if (Storage::disk($disk)->exists($path)) {
                    return [
                        'disk' => $disk,
                        'path' => $path,
                        'absolute' => Storage::disk($disk)->path($path),
                    ];
                }
            } catch (\Throwable $e) {
                // Try the next known location rather than breaking the page.
            }
        }

        // Compatibility for files uploaded directly to public/uploads by
        // older deployments (no storage symlink involved).
        $absolute = realpath(public_path($path));
        $publicRoot = realpath(public_path());
        if ($absolute && $publicRoot
            && strpos($absolute, $publicRoot . DIRECTORY_SEPARATOR) === 0
            && is_file($absolute)) {
            return [
                'disk' => 'absolute',
                'path' => $path,
                'absolute' => $absolute,
            ];
        }

        return null;
    }

    public static function exists($storedPath): bool
    {
        return static::resolve($storedPath) !== null;
    }
}
