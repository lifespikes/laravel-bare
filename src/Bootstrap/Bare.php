<?php

namespace LifeSpikes\LaravelBare\Bootstrap;

use RuntimeException;
use Illuminate\Http\Request;
use Illuminate\Contracts\Http\Kernel;
use LifeSpikes\LaravelBare\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;

class Bare
{
    public Application $application;

    public function __construct(
        public array|string $baseOrConfig = [],
    ) {
        pathfinder()->setAppPaths(
            is_string($this->baseOrConfig)
                ? ['base' => $this->baseOrConfig]
                : $this->baseOrConfig
        );
    }

    public function laravel()
    {
        if (!isset($this->application)) {
            if (!class_exists('Composer\\Autoload\\ClassLoader')) {
                throw new RuntimeException('Bare requires Composer to be loaded prior to bootstrap.');
            }

            if (!defined('LARAVEL_START')) {
                define('LARAVEL_START', microtime(true));
            }

            return ($this->application = require_once __DIR__ . '/../../bootstrap/app.php');
        }

        return $this->application;
    }

    public function artisan(): void
    {
        $app = $this->laravel();
        $kernel = $app->make(ConsoleKernel::class);

        $status = $kernel->handle(
            $input = new ArgvInput(),
            new ConsoleOutput()
        );

        $kernel->terminate($input, $status);

        exit($status);
    }

    public function web(): void
    {
        $app = $this->laravel();
        $kernel = $app->make(Kernel::class);

        $response = $kernel->handle(
            $request = Request::capture()
        )->send();

        $kernel->terminate($request, $response);
    }
}
