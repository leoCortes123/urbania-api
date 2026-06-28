<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\DTOs;

use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class UploadPropertyDocumentRequestDto
{
    public function __construct(
        public Uuid $propertyId,
        public Uuid $propertyDocumentTypeId,
        public string $name,
        public string $filePath,
        public Uuid $uploadedByUserId,
        public ?string $notes,
    ) {}
}
