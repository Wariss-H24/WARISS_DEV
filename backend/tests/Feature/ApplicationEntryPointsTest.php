<?php

it('returns the application name and the API base URL at the root', function () {
    $response = $this->getJson('/');

    $response->assertOk()
        ->assertExactJsonStructure(['name', 'api'])
        ->assertJsonPath('name', 'WARISS.DEV')
        ->assertJsonPath('api', fn (string $url): bool => str_ends_with($url, '/api/v1'));
});

it('returns 200 from the health check endpoint', function () {
    $response = $this->get('/up');

    $response->assertOk();
});

it('returns a JSON 404 for an unknown API route even without a JSON Accept header', function () {
    $response = $this->get('/api/v1/route-inexistante');

    $response->assertNotFound()
        ->assertHeader('Content-Type', 'application/json')
        ->assertJsonStructure(['message']);
});
