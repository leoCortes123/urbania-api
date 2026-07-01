<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Persistence;

use App\Models\AnnouncementDelivery as AnnouncementDeliveryModel;
use Urbania\Comunicaciones\Domain\Entities\AnnouncementDeliveryEntity;
use Urbania\Comunicaciones\Domain\Repositories\AnnouncementDeliveryRepositoryInterface;
use Urbania\Comunicaciones\Infrastructure\Mappers\AnnouncementDeliveryMapper;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class EloquentAnnouncementDeliveryRepository implements AnnouncementDeliveryRepositoryInterface
{
    public function __construct(
        private AnnouncementDeliveryMapper $mapper,
    ) {}

    public function findById(Uuid $id): ?AnnouncementDeliveryEntity
    {
        $model = AnnouncementDeliveryModel::find($id->toString());

        return $model !== null ? $this->mapper->toDomain($model) : null;
    }

    /**
     * @return array<AnnouncementDeliveryEntity>
     */
    public function findByAnnouncementId(Uuid $announcementId): array
    {
        $models = AnnouncementDeliveryModel::where('announcement_id', $announcementId->toString())->get();

        return $models->map(fn (AnnouncementDeliveryModel $m) => $this->mapper->toDomain($m))->all();
    }

    public function save(AnnouncementDeliveryEntity $entity): void
    {
        $data = $this->mapper->toPersistence($entity);

        AnnouncementDeliveryModel::updateOrCreate(
            ['id' => $entity->id()->toString()],
            $data,
        );
    }

    /**
     * @return array{enviados: int, entregados: int, leidos: int}
     */
    public function metricsByAnnouncementId(Uuid $announcementId): array
    {
        $query = AnnouncementDeliveryModel::where('announcement_id', $announcementId->toString());

        return [
            'enviados' => (int) (clone $query)->where('estado', 'enviado')->count(),
            'entregados' => (int) (clone $query)->where('estado', 'entregado')->count(),
            'leidos' => (int) (clone $query)->where('estado', 'leido')->count(),
        ];
    }

    /**
     * @return array{byStatus: array<string, int>, byChannel: array<string, array<string, int>>}
     */
    public function breakdownByAnnouncementId(Uuid $announcementId): array
    {
        $rows = AnnouncementDeliveryModel::where('announcement_id', $announcementId->toString())
            ->selectRaw('canal, estado, count(*) as total')
            ->groupBy('canal', 'estado')
            ->get();

        /** @var array<string, int> $byStatus */
        $byStatus = [];
        /** @var array<string, array<string, int>> $byChannel */
        $byChannel = [];

        foreach ($rows as $row) {
            /** @var string $canal */
            $canal = $row->canal;
            /** @var string $estado */
            $estado = $row->estado;
            $totalRaw = $row->getAttribute('total');
            $total = is_numeric($totalRaw) ? (int) $totalRaw : 0;

            $byStatus[$estado] = ($byStatus[$estado] ?? 0) + $total;

            if (! isset($byChannel[$canal])) {
                $byChannel[$canal] = [];
            }

            $byChannel[$canal][$estado] = ($byChannel[$canal][$estado] ?? 0) + $total;
        }

        return [
            'byStatus' => $byStatus,
            'byChannel' => $byChannel,
        ];
    }
}
