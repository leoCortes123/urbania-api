<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\Services;

interface AvatarStorageServiceInterface
{
    /**
     * Store a base64 encoded image and return its public URL.
     */
    public function store(string $base64Image): string;
}
