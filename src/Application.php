<?php
/*
|--------------------------------------------------------------------------
| Monorepo Laravel Container
|--------------------------------------------------------------------------
|
| This class is a workaround to the lack of support for custom path patterns
| for certain Laravel systems. So far it has not presented problems when using
| Artisan and its caching functionalities, which are what these changes
| mostly affect. But definitely feel free to try to break it.
|
*/

namespace LifeSpikes\LaravelBare;

use JsonException;
use Illuminate\Support\Env;
use Illuminate\Support\Str;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use function LifeSpikes\LaravelBare\Bootstrap\pathfinder;

class Application extends \Illuminate\Foundation\Application
{
    protected $namespace;
    protected PathFinder $pathFinder;

    public function __construct($basePath = null)
    {
        $this->pathFinder = pathfinder();
        $this->namespace = $this->pathFinder->appNamespace();

        parent::__construct($basePath ?? $this->pathFinder->app('base'));

        $this->beforeBootstrapping(\Illuminate\Foundation\Bootstrap\RegisterProviders::class, function () {
            $this->loadRootProviders();
        });
    }

    private function loadRootProviders()
    {
        $manifest = json_decode(
            file_get_contents($this->pathFinder->app('base', 'composer.json')),
            true
        );

        array_map(
            fn (string $provider) => $this->register($provider),
            data_get($manifest, 'extra.laravel.providers', [])
        );
    }

    public function path($path = ''): string
    {
        return $this->pathFinder->app('app', $path);
    }

    public function publicPath(): string
    {
        return $this->pathFinder->app('public');
    }

    public function storagePath($path = ''): string
    {
        return $this->pathFinder->app('storage', $path);
    }

    public function databasePath($path = ''): string
    {
        return $this->pathFinder->app('database', $path);
    }

    public function viewPath($path = ''): string
    {
        return $this->pathFinder->app('views', $path);
    }

    public function resourcePath($path = ''): string
    {
        return $this->pathFinder->app('resources', $path);
    }

    public function bootstrapPath($path = ''): string
    {
        return $this->pathFinder->kernel('bootstrap', $path);
    }

    public function configPath($path = ''): string
    {
        return $this->pathFinder->kernel('config', $path);
    }

    public function langPath($path = ''): string
    {
        return $this->pathFinder->kernel('lang', $path);
    }

    public function environmentPath(): string
    {
        return $this->pathFinder->app('base');
    }

    /**
     * Override default behavior, instead of caching in
     * the bootstrap, we cache in the framework storage
     * directory.
     *
     * @param $key
     * @param $default
     * @return string
     */
    protected function normalizeCachePath($key, $default): string
    {
        if (is_null($env = Env::get($key))) {
            return $this->pathFinder->app('cache', $default);
        }

        return Str::startsWith($env, $this->absoluteCachePathPrefixes)
            ? $env
            : $this->basePath($env);
    }
}
