<?php

declare(strict_types=1);

namespace Battlescribe\Roster;

interface SelectionInterface
{
    public function getName(): string;

    public function hasCategory(Category $category): bool;

    public function getCategories(): array;

    /** @return SelectionInterface[] */
    public function getSelections(): array;
}
