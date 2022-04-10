<?php

namespace LifeSpikes\LaravelBare\Bootstrap;

use RuntimeException;
use Illuminate\Http\Request;
use Composer\Autoload\ClassLoader;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class Bare
{
    public function __construct(
        public array|string $baseOrConfig = [],
    ) {
        pathfinder()->setAppPaths(
        is_string($this->baseOrConfig)
            ? ['base' => $this->baseOrConfig]
            : $this->baseOrConfig
        );
    }

    private function laravel()
    {
        if (!class_exists(ClassLoader::class)) {
            throw new RuntimeException('Bare requires Composer to be loaded prior to bootstrap.');
        }

        if (!defined('LARAVEL_START')) {
            throw new RuntimeException('Please define LARAVEL_START prior to bootstrap.');
        }

        return require_once __DIR__ . '/../../bootstrap/app.php';
    }

    public function artisan()
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

    public function web()
    {
        $app = $this->laravel();
        $kernel = $app->make(Kernel::class);

        $response = $kernel->handle(
            $request = Request::capture()
        )->send();

        $kernel->terminate($request, $response);
    }
}
