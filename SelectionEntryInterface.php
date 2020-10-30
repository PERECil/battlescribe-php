<?php

declare(strict_types=1);

namespace Battlescribe;

use JsonSerializable;

interface SelectionEntryInterface extends JsonSerializable
{
    public function getId(): string;

    public function getName(): string;

    public function isHidden(): bool;

    public function isCollective(): bool;

    public function isImport(): bool;

    public function getType(): SelectionEntryType;

    /**
     * @return Modifier[]
     */
    public function getModifiers(): array;

    /**
     * @return Constraint[]
     */
    public function getConstraints(): array;

    public function findConstraint(string $id): ?ConstraintInterface;

    /**
     * @return Profile[]
     */
    public function getProfiles(): array;

    /**
     * @return InfoLink[]
     */
    public function getInfoLinks(): array;

    /**
     * @return CategoryLink[]
     */
    public function getCategoryLinks(): array;

    /**
     * @return SelectionEntryGroup[]
     */
    public function getSelectionEntryGroups(): array;

    /**
     * @return SelectionEntry[]
     */
    public function getSelectionEntries(): array;

    /**
     * @return EntryLink[]
     */
    public function getEntryLinks(): array;

    /**
     * @return Cost[]
     */
    public function getCosts(): array;
}
