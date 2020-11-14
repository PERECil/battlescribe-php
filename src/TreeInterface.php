<?php

declare(strict_types=1);

namespace Battlescribe;

interface TreeInterface
{
    public function getParent(): ?TreeInterface;

    /** @return TreeInterface[] */
    public function getChildren(): array;
}
