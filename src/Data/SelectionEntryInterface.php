<?php

declare(strict_types=1);

namespace Battlescribe\Data;

interface SelectionEntryInterface extends IdentifierInterface, TreeInterface, HideableInterface, ModifiableInterface, ShareableInterface, NameInterface, CategorizableInterface, SelectableInterface
{
    public function isCollective(): bool;

    public function isImport(): bool;

    public function getType(): SelectionEntryType;

    /** @return Modifier[] */
    public function getModifiers(): array;

    /** @return Constraint[] */
    public function getConstraints(): array;

    /** @return RuleInterface[] */
    public function getRules(): array;

    public function findConstraint(Identifier $id): ?ConstraintInterface;

    public function findCost(Identifier $id): ?CostInterface;

    /** @return Profile[] */
    public function getProfiles(): array;

    /** @return InfoLink[] */
    public function getInfoLinks(): array;

    /** @return SelectionEntryGroupInterface[] */
    public function getSelectionEntryGroups(): array;

    /** @return SelectionEntry[] */
    public function getSelectionEntries(): array;

    /** @return EntryLink[] */
    public function getEntryLinks(): array;

    /** @return Cost[] */
    public function getCosts(): array;

    /** @return CategoryEntryInterface[] */
    public function getCategoryEntries(): array;
}
