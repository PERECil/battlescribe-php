<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Closure;

trait LeafTrait
{
    private ?TreeInterface $parent;

    public function getParent(): ?TreeInterface
    {
        return $this->parent;
    }

    public function getChildren(): array
    {
        return [];
    }

    public function getRoot(): TreeInterface
    {
        return $this->parent === null ? $this : $this->parent->getRoot();
    }

    public function getGameSystem(): ?GameSystem
    {
        return $this->parent?->getGameSystem();
    }

    public function findByMatcher(Closure $matcher): array
    {
        return [];
    }
}
