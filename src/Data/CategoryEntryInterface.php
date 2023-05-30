<?php

declare(strict_types=1);

namespace Battlescribe\Data;

interface CategoryEntryInterface extends HideableInterface, IdentifierInterface, ShareableInterface, TreeInterface
{
    public function isPrimary(): bool;
}
