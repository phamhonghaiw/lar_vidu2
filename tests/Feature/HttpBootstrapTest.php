<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class HttpBootstrapTest extends TestCase
{
    public function test_http_kernel_starts_before_console_bootstrap(): void
    {
        // Laravel's regular feature tests bootstrap the console kernel first.
        // A separate process reproduces the startup order used by public/index.php.
        $process = new Process([PHP_BINARY, '-r', <<<'PHP'
            require 'vendor/autoload.php';
            $app = require 'bootstrap/app.php';
            $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
            $request = Illuminate\Http\Request::create('http://localhost/up');
            $response = $kernel->handle($request);
            echo 'HTTP '.$response->getStatusCode();
            $kernel->terminate($request, $response);
            exit($response->getStatusCode() === 200 ? 0 : 1);
            PHP,
        ], dirname(__DIR__, 2), ['APP_DEBUG' => 'false']);

        $process->setTimeout(30);
        $process->run();

        $this->assertTrue($process->isSuccessful(), $process->getErrorOutput().$process->getOutput());
        $this->assertSame('HTTP 200', $process->getOutput());
    }
}
