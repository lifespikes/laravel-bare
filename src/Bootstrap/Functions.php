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
