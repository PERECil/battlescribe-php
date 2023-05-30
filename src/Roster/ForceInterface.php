<?php

declare(strict_types=1);

namespace Battlescribe\Roster;

use Battlescribe\Data\CategorizableInterface;
use Battlescribe\Data\TreeInterface;

interface ForceInterface extends TreeInterface, CategorizableInterface
{
    public function getCatalogueName(): string;

    public function addSelection(SelectionInterface $selection): void;

    /** @return SelectionInterface[] */
    public function getSelections(): array;

    /** @return Category[] */
    public function getCategories(): array;
}
