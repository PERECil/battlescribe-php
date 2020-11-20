<?php

declare(strict_types=1);

namespace Battlescribe\Data;

interface HideableInterface
{
    public function isHidden(): bool;
}
