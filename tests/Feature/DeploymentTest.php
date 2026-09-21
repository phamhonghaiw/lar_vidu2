<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DeploymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_fresh_database_supports_the_homepage_and_health_check(): void
    {
        $this->get('/')->assertOk();
        $this->get('/up')->assertOk();
    }

    public function test_database_sessions_can_be_read_by_a_new_handler(): void
    {
        $id = str_repeat('a', 40);
        $payload = serialize(['cart' => [1 => ['quantity' => 2]]]);
        $writer = new DatabaseSessionHandler(DB::connection(), 'sessions', 120, $this->app);
        $this->assertTrue($writer->write($id, $payload));

        $reader = new DatabaseSessionHandler(DB::connection(), 'sessions', 120, $this->app);
        $this->assertSame($payload, $reader->read($id));
        $reader->destroy($id);
    }

    public function test_trusted_proxy_generates_https_form_urls(): void
    {
        // The HTTP kernel applies config/deployment.php when it resolves middleware.
        config(['deployment.trusted_proxies' => '*']);

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.10'])
            ->withHeaders(['X-Forwarded-Proto' => 'https', 'X-Forwarded-Port' => '443'])
            ->get('http://shop.example/login')
            ->assertOk()
            ->assertSee('action="https://shop.example/login"', false);
    }

    public function test_untrusted_proxy_cannot_change_form_url_scheme(): void
    {
        config(['deployment.trusted_proxies' => []]);

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.10'])
            ->withHeaders(['X-Forwarded-Proto' => 'https', 'X-Forwarded-Port' => '443'])
            ->get('http://shop.example/login')
            ->assertOk()
            ->assertSee('action="http://shop.example/login"', false);
    }
}
