<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Closure;

interface TreeInterface
{
    public function getParent(): ?TreeInterface;

    /**
     * Gets the shallow list of children.
     * @return TreeInterface[]
     */
    public function getChildren(): array;

    public function getRoot(): TreeInterface;

    public function getGameSystem(): ?GameSystem;

    /** @return TreeInterface[] */
    public function findByMatcher(Closure $matcher): array;
}
