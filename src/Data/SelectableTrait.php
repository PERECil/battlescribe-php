<?php

declare(strict_types=1);

namespace Battlescribe\Data;

/** Makes an object selectable */
trait SelectableTrait
{
    private int $selectedCount = 0;
    private ?int $minimumSelectedCount = null;
    private ?int $maximumSelectedCount = null;

    public function setSelectedCount(int $selectedCount): void
    {
        $this->selectedCount = $selectedCount;
    }

    public function getSelectedCount(): int
    {
        return $this->selectedCount;
    }

    public function setMinimumSelectedCount(int $minimumSelectedCount): void
    {
        $this->minimumSelectedCount = $minimumSelectedCount;
    }

    public function getMinimumSelectedCount(): ?int
    {
        return $this->minimumSelectedCount;
    }

    public function setMaximumSelectedCount(int $maximumSelectedCount): void
    {
        $this->maximumSelectedCount = $maximumSelectedCount;
    }

    public function getMaximumSelectedCount(): ?int
    {
        return $this->maximumSelectedCount;
    }
}
