<?php

declare(strict_types=1);

namespace Battlescribe\Data;

interface CostInterface extends TreeInterface, NameInterface
{
    public function getTypeId(): ?Identifier;

    public function getValue(): float;
}
