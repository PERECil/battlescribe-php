<?php

declare(strict_types=1);

namespace Battlescribe\Data;

interface SelectableInterface
{
    public function setSelectedCount(int $selectedCount): void;

    public function getSelectedCount(): int;

    public function setMinimumSelectedCount(int $minimumSelectedCount): void;

    public function getMinimumSelectedCount(): ?int;

    public function setMaximumSelectedCount(int $maximumSelectedCount): void;

    public function getMaximumSelectedCount(): ?int;
}
