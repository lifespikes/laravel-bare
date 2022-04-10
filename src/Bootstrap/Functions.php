<?php

namespace LifeSpikes\LaravelBare\Bootstrap;

use LifeSpikes\LaravelBare\PathFinder;

function bare(array|string $baseOrConfig = []): Bare
{
    return new Bare($baseOrConfig);
}

function pathfinder(): PathFinder
{
    return PathFinder::getInstance();
}

function array_merge_recursive_distinct(array &$array1, array &$array2): array
{
    $merged = $array1;
    foreach ($array2 as $key => &$value) {
        if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
            $merged[$key] = array_merge_recursive_distinct($merged[$key], $value);
        } else {
            $merged[$key] = $value;
        }
    }

    return $merged;
}
