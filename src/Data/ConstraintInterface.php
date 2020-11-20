<?php

declare(strict_types=1);

namespace Battlescribe\Data;

interface ConstraintInterface
{
    public function getId(): string;

    public function getField(): string;

    public function getScope(): string;

    public function getValue(): float;

    public function isPercentValue(): bool;

    public function isShared(): bool;

    public function includeChildSelections(): bool;

    public function includeChildForces(): bool;

    public function getType(): ConstraintType;

    /**
     * @return ConditionGroup[]
     */
    public function getConditionGroups(): array;
}
