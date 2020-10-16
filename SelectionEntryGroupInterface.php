<?php

declare(strict_types=1);

namespace Battlescribe;

use JsonSerializable;

interface SelectionEntryGroupInterface extends JsonSerializable
{
    public function getId(): string;

    public function getName(): string;

    public function isHidden(): bool;

    public function isCollective(): bool;

    public function isImport(): bool;

    public function getDefaultSelectionEntryId(): ?string;

    /**
     * @return Constraint[]
     */
    public function getConstraints(): array;

    /**
     * @return SelectionEntry[]
     */
    public function getSelectionEntries(): array;

    /**
     * @return SelectionEntryGroup[]
     */
    public function getSelectionEntryGroups(): array;

    /**
     * @return EntryLink[]
     */
    public function getEntryLinks(): array;
}
