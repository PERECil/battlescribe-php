<?php

declare(strict_types=1);


namespace Battlescribe;

interface CategoryEntryInterface extends HideableInterface, IdentifierInterface, ShareableInterface
{
    public function isPrimary(): bool;
}
