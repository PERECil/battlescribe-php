<?php

declare(strict_types=1);

namespace Battlescribe;

interface CostInterface
{
    public function getTypeId(): string;

    public function getValue(): float;
}
