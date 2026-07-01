<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\UseCases\Announcements;

use Urbania\Comunicaciones\Domain\Exceptions\AnnouncementNotFoundException;
use Urbania\Comunicaciones\Domain\Repositories\AnnouncementRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class DeleteAnnouncementUseCase
{
    public function __construct(
        private AnnouncementRepositoryInterface $announcementRepository,
    ) {}

    public function execute(Uuid $id): void
    {
        $entity = $this->announcementRepository->findById($id);

        if ($entity === null) {
            throw new AnnouncementNotFoundException;
        }

        $this->announcementRepository->delete($id);
    }
}
