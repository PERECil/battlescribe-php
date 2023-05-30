<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Closure;

trait BranchTrait
{
    use FindByMatcherTrait;

    private ?TreeInterface $parent;

    public function getParent(): ?TreeInterface
    {
        return $this->parent;
    }

    public function attachTo(TreeInterface $parent): void
    {
        $this->parent = $parent;
    }

    public function getRoot(): TreeInterface
    {
        return $this->parent === null ? $this : $this->parent->getRoot();
    }

    public function getGameSystem(): ?GameSystem
    {
        return $this->parent?->getGameSystem();
    }
}
