<?php

declare(strict_types=1);

namespace Battlescribe\Data;

interface CategoryEntryInterface extends HideableInterface, IdentifierInterface, ShareableInterface
{
    public function isPrimary(): bool;
}
