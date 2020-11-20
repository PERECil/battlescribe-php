<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Closure;

interface TreeInterface
{
    public function getParent(): ?TreeInterface;

    /** @return TreeInterface[] */
    public function getChildren(): array;

    public function getRoot(): TreeInterface;

    /** @return SelectionEntryInterface[] */
    public function findSelectionEntryByMatcher(Closure $matcher): array;
}
