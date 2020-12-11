<?php

declare(strict_types=1);

namespace Battlescribe\Roster;

use Battlescribe\Data\ConstraintInterface;
use Battlescribe\Data\ConstraintType;

class ConstraintInstance implements ConstraintInterface
{
    private ?float $valueOverride = null;

    private ConstraintInterface $implementation;
    private string $instanceId;

    public function __construct(ConstraintInterface $implementation)
    {
        $this->implementation = $implementation;
        $this->instanceId = spl_object_hash($this);
    }

    public function getInstanceId(): string
    {
        return $this->instanceId;
    }

    public function setValue(?float $value): void
    {
        $this->valueOverride = $value;
    }

    public function getValue(): float
    {
        return $this->valueOverride ?? $this->implementation->getValue();
    }

    public function getId(): string
    {
        return $this->implementation->getId();
    }

    public function getField(): string
    {
        return $this->implementation->getField();
    }

    public function getScope(): string
    {
        return $this->implementation->getScope();
    }

    public function isPercentValue(): bool
    {
        return $this->implementation->isPercentValue();
    }

    public function isShared(): bool
    {
        return $this->implementation->isShared();
    }

    public function includeChildSelections(): bool
    {
        return $this->implementation->includeChildSelections();
    }

    public function includeChildForces(): bool
    {
        return $this->implementation->includeChildForces();
    }

    public function getType(): ConstraintType
    {
        return $this->implementation->getType();
    }

    public function getConditionGroups(): array
    {
        return $this->implementation->getConditionGroups();
    }
}
