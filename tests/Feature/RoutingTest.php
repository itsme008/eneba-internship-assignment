<?php

namespace Tests\Feature;

use Tests\TestCase;

class RoutingTest extends TestCase
{
    public function test_deep_frontend_routes_render_shell_and_unknown_api_routes_remain_json(): void
    {
        $this->withoutVite();
        $this->get('/products/elden-ring-global')->assertOk()->assertSee('id="root"', false);
        $this->getJson('/api/v1/does-not-exist')->assertNotFound()->assertJsonPath('error.code', 'NOT_FOUND');
        $this->call('POST', '/api/v1/cart/preview', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'], '{broken')->assertBadRequest();
    }

    public function test_mutations_require_csrf_outside_test_bypass(): void
    {
        $this->app['env'] = 'local';
        $this->postJson('/api/v1/cart/preview', ['items' => []])->assertStatus(419);
    }
}
