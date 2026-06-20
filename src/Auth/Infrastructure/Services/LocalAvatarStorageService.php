<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Services;

use Illuminate\Support\Facades\Storage;
use Urbania\Auth\Application\Services\AvatarStorageServiceInterface;

final readonly class LocalAvatarStorageService implements AvatarStorageServiceInterface
{
    public function store(string $base64Image): string
    {
        $decoded = base64_decode($base64Image, true);

        if ($decoded === false) {
            throw new \InvalidArgumentException('Invalid base64 image');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($decoded);

        $extension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw new \InvalidArgumentException('Unsupported image type'),
        };

        $fileName = 'avatars/'.uniqid('', true).'.'.$extension;

        Storage::disk('public')->put($fileName, $decoded);

        return Storage::disk('public')->url($fileName);
    }
}
