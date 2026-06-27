<?php

declare(strict_types=1);

it('returns 204 for cors preflight requests to api endpoints', function (): void {
    $response = $this->options('/api/v1/health');

    $response->assertNoContent()
        ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173')
        ->assertHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
        ->assertHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Trace-Id, X-Requested-With')
        ->assertHeader('Access-Control-Allow-Credentials', 'true')
        ->assertHeader('Access-Control-Max-Age', '86400');
});

it('includes cors headers on api responses', function (): void {
    $response = $this->getJson('/api/v1/health');

    $response->assertOk()
        ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173')
        ->assertHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
        ->assertHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Trace-Id, X-Requested-With')
        ->assertHeader('Access-Control-Allow-Credentials', 'true')
        ->assertHeader('Access-Control-Max-Age', '86400');
});

it('uses configured allowed origins from environment', function (): void {
    config(['cors.allowed_origins' => 'http://localhost:3000']);

    $response = $this->getJson('/api/v1/health');

    $response->assertOk()
        ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000');
});
