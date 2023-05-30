<?php

declare(strict_types=1);


namespace Battlescribe\Data;

interface CategorizableInterface
{
    /** @return CategoryLink[] */
    public function getCategoryLinks(): array;
}
