<?php

declare(strict_types=1);

namespace Battlescribe\Data;

interface SelectionEntryGroupInterface extends IdentifierInterface, TreeInterface, HideableInterface, ModifiableInterface, ShareableInterface, NameInterface
{
    public function isCollective(): bool;

    public function isImport(): bool;

    public function getDefaultSelectionEntryId(): ?Identifier;

    /** @return ConstraintInterface[] */
    public function getConstraints(): array;

    /** @return SelectionEntryInterface[] */
    public function getSelectionEntries(): array;

    /** @return SelectionEntryGroupInterface[] */
    public function getSelectionEntryGroups(): array;

    /** @return EntryLink[] */
    public function getEntryLinks(): array;

    /** @return Modifier[] */
    public function getModifiers(): array;
}
