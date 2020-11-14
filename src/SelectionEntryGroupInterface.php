<?php

declare(strict_types=1);

namespace Battlescribe;

use JsonSerializable;

interface SelectionEntryGroupInterface extends IdentifierInterface, TreeInterface, HideableInterface, ModifiableInterface, ShareableInterface
{
    public function getName(): string;

    public function isCollective(): bool;

    public function isImport(): bool;

    public function getDefaultSelectionEntryId(): ?string;

    /** @return Constraint[] */
    public function getConstraints(): array;

    /** @return SelectionEntry[] */
    public function getSelectionEntries(): array;

    /** @return SelectionEntryGroup[] */
    public function getSelectionEntryGroups(): array;

    /** @return EntryLink[] */
    public function getEntryLinks(): array;

    /** @return Modifier[] */
    public function getModifiers(): array;
}
