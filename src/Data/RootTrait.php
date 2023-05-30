<?php

declare(strict_types=1);


namespace Battlescribe\Data;

use Closure;

trait RootTrait
{
    use FindByMatcherTrait;

    public function getParent(): ?TreeInterface
    {
        return null;
    }

    public function getRoot(): TreeInterface
    {
        return $this;
    }

    public function getGameSystem(): ?GameSystem
    {
        return $this instanceof GameSystem ? $this : null;
    }
}
