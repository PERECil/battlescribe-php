<?php

declare(strict_types=1);

namespace Battlescribe\Data;

interface ShareableInterface
{
    public function getSharedId(): Identifier;
}
