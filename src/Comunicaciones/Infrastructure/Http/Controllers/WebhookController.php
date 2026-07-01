<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Http\Controllers;

use App\Models\AnnouncementDelivery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class WebhookController extends Controller
{
    public function process(string $provider, Request $request): JsonResponse
    {
        $externalId = $request->input('external_id');
        $status = $request->input('status');

        if (is_string($externalId) && $externalId !== '' && is_string($status) && $status !== '') {
            AnnouncementDelivery::where('external_id', $externalId)->update([
                'estado' => $status,
                'metadata' => $request->all(),
            ]);
        }

        return response()->json(['ok' => true]);
    }
}
