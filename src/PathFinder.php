<?php

namespace LifeSpikes\LaravelBare;

use Closure;
use RuntimeException;

class PathFinder
{
    private array $config;

    static $_instance;

    public function __construct(array $paths = [])
    {
        $this->config = require_once __DIR__.'/../config/paths.php';
    }

    public static function getInstance(): static
    {
        if (self::$_instance === null) {
            self::$_instance = new static();
        }

        return self::$_instance;
    }

    public function setAppPaths(array $paths)
    {
        $merge = ['app' => $paths];

        $this->config = \LifeSpikes\LaravelBare\Bootstrap\array_merge_recursive_distinct(
            $this->config,
            $merge
        );
    }

    public function appNamespace(): string
    {
        return $this->config['app']['namespace'];
    }

    public function kernel(string $key, string $path = ''): string
    {
        return $this->resolve($key, $path, 'kernel');
    }

    public function app(string $key, string $path = ''): string
    {
        return $this->resolve($key, $path);
    }

    public function resolve(string $key, string $path = '', string $type = 'app'): string
    {
        $config = $this->config;
        $files = $config[$type];
        $directory = realpath("$files[base]/{$files['paths'][$key]}");

        return rtrim($directory . '/' . ltrim($path ), '/');
    }
}
