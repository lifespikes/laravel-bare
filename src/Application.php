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

use Illuminate\Support\Env;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\PackageManifest as Manifest;
use Illuminate\Foundation\Bootstrap\RegisterProviders;
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

        $this->useLangPath($this->langPath());

        /* Load monorepo providers */

        $this->singleton(Manifest::class, $this->getManifestClosure());
    }

    public function getManifestClosure(): \Closure
    {
        return function ($app) {
            $args = [new Filesystem, $this->basePath(), $this->getCachedPackagesPath()];

            return new class(...$args) extends Manifest {
                public function write(array $manifest)
                {
                    $providers = $this->getMonorepoProviders();
                    $label = count($providers).' monorepo packages';

                    $manifest[$label] = [
                        'providers' =>  $providers,
                        'aliases'   =>  [],
                    ];

                    parent::write($manifest);
                }

                private function getMonorepoProviders()
                {
                    return data_get(
                        json_decode(
                            file_get_contents($this->basePath.'/composer.json'),
                            true
                        ),
                        'extra.laravel.providers',
                        []
                    );
                }
            };
        };
    }

    public function path($path = ''): string
    {
        return $this->pathFinder->app('app', $path);
    }

    public function basePath($path = ''): string
    {
        return $this->pathFinder->app('base', $path);
    }

    public function publicPath($path = 'public'): string
    {
        return $this->pathFinder->app($path);
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

    /**
     * Register multiple binds at once.
     * @param array $binds
     */
    public function binds(array $binds): void
    {
        foreach ($binds as $abstract => $concrete) {
            $this->bind($abstract, $concrete);
        }
    }
}
