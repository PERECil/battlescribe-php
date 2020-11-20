<?php

declare(strict_types=1);

namespace Battlescribe\Roster;

use Battlescribe\Data\CostInterface;

class CostInstance implements CostInterface
{
    private ?float $valueOverride = null;

    private CostInterface $implementation;

    public function __construct(CostInterface $implementation)
    {
        $this->implementation = $implementation;
    }

    public function getTypeId(): string
    {
        return $this->implementation->getTypeId();
    }

    public function setValue(?float $value): void
    {
        $this->valueOverride = $value;
    }

    public function getValue(): float
    {
        return $this->valueOverride ?? $this->implementation->getValue();
    }
}
