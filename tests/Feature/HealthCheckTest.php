<?php

declare(strict_types=1);

use function Pest\Laravel\getJson;

it('returns healthy status when all services are available', function () {
    $response = getJson('/api/v1/health');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'status',
                'timestamp',
                'checks' => [
                    'database' => ['healthy', 'message'],
                    'redis' => ['healthy', 'message'],
                    'storage' => ['healthy', 'message'],
                ],
            ],
            'meta' => ['trace_id'],
        ])
        ->assertJsonPath('data.status', 'healthy');
});

it('returns the correct JSON structure for health check', function () {
    $response = getJson('/api/v1/health');

    $response->assertJsonStructure([
        'data' => [
            'status',
            'timestamp',
            'checks',
        ],
        'meta' => [
            'trace_id',
        ],
    ]);
});

it('includes timestamp in ISO 8601 UTC format', function () {
    $response = getJson('/api/v1/health');

    $timestamp = $response->json('data.timestamp');
    expect($timestamp)->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/');
});

it('includes a valid trace_id in meta', function () {
    $response = getJson('/api/v1/health');

    $traceId = $response->json('meta.trace_id');
    expect($traceId)->toBeString()->not->toBeEmpty();
});

it('includes security headers', function () {
    $response = getJson('/api/v1/health');

    expect($response->headers->get('Strict-Transport-Security'))->not->toBeEmpty();
});
