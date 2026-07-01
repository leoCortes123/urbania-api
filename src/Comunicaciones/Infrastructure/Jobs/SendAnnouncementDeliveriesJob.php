<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Jobs;

use App\Models\Announcement;
use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Urbania\Comunicaciones\Domain\ValueObjects\DeliveryStatus;

final class SendAnnouncementDeliveriesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly string $announcementId,
    ) {}

    public function handle(): void
    {
        $announcement = Announcement::find($this->announcementId);

        if ($announcement === null) {
            return;
        }

        /** @var array<string> $canales */
        $canales = $announcement->canales ?? [];

        $contacts = Contact::query()->limit(1000)->get();

        foreach ($contacts as $contact) {
            foreach ($canales as $canal) {
                $announcement->deliveries()->create([
                    'contact_id' => $contact->id,
                    'canal' => $canal,
                    'estado' => DeliveryStatus::ENVIADO->value,
                ]);
            }
        }
    }

    /**
     * @return array<int, string>
     */
    public function middleware(): array
    {
        return [];
    }
}
