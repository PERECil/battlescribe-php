<?php

declare(strict_types=1);

namespace Battlescribe\Roster;

use Battlescribe\Data\ConstraintType;
use Battlescribe\Data\SelectionEntryGroupInterface;
use Battlescribe\Data\SelectionEntryInterface;
use Battlescribe\Data\SelectionEntryType;
use Battlescribe\Data\TreeInterface;
use Closure;

class SelectionEntryInstance implements SelectionEntryInterface
{
    private TreeInterface $parent;

    private string $instanceId;

    private SelectionEntryInterface $implementation;

    private ?string $nameOverride;
    private ?bool $hiddenOverride;

    /** @var SelectionEntryInstance[] */
    private array $selectionEntries;

    /** @var SelectionEntryGroupInstance[] */
    private array $selectionEntryGroups;

    /** @var ConstraintInstance[] */
    private array $constraints;

    /** @var CostInstance[] */
    private array $costs;

    private int $selectedCount;
    private ?int $minimumSelectedCount;
    private ?int $maximumSelectedCount;

    public function __construct(TreeInterface $parent, SelectionEntryInterface $implementation)
    {
        $this->parent = $parent;
        $this->instanceId = spl_object_hash($this);
        $this->implementation = $implementation;

        $this->nameOverride = null;
        $this->hiddenOverride = null;

        $this->selectionEntries = [];

        foreach($this->implementation->getSelectionEntries() as $selectionEntry) {
            $this->selectionEntries[] = new SelectionEntryInstance($this, $selectionEntry);
        }

        $this->selectionEntryGroups = [];

        foreach($this->implementation->getSelectionEntryGroups() as $selectionEntryGroup) {
            $this->selectionEntryGroups[] = new SelectionEntryGroupInstance($this, $selectionEntryGroup);
        }

        // Should be before constraints initialization as constraints may modify the cost
        $this->costs = [];

        foreach($this->implementation->getCosts() as $cost) {
            $this->costs[] = new CostInstance($cost);
        }

        // Needs to have instances because modifiers can modify constraints
        $this->constraints = [];

        foreach($this->implementation->getConstraints() as $constraint) {
            $this->constraints[] = new ConstraintInstance($constraint);
        }

        // If there is a selection entry group as a parent, the item is selected only if it's the default selection
        if($this->parent instanceof SelectionEntryGroupInterface) {
            $this->selectedCount = $this->parent->getDefaultSelectionEntryId() === $this->implementation->getSharedId() ? 1 : 0;
        } else {
            $this->selectedCount = 1;
        }

        $this->minimumSelectedCount = null;
        $this->maximumSelectedCount = null;
    }

    /**
     * @param SelectionEntryInstance[] $selectionEntries
     */
    public function computeState(array $selectionEntries): void
    {
        foreach($this->implementation->getConstraints() as $constraint) {
            $constraint->applyTo($selectionEntries, $this);
        }

        foreach($this->implementation->getModifiers() as $modifier) {
            $modifier->applyTo($selectionEntries, $this);
        }

        foreach($this->selectionEntries as $selectionEntry) {
            $selectionEntry->computeState($selectionEntries);
        }

        foreach($this->selectionEntryGroups as $selectionEntryGroup) {
            $selectionEntryGroup->computeState($selectionEntries);
        }
    }

    /** @return SelectionEntryGroupInstance[] */
    public function findSelectionEntryGroupByName(string $name): array
    {
        $result = [];

        foreach($this->selectionEntryGroups as $selectionEntryGroup) {
            if($selectionEntryGroup->getName() === $name ) {
                $result[] = $selectionEntryGroup;
            }
        }

        return $result;
    }

    public function findSelectionEntryGroupByInstanceId(string $instanceId): ?SelectionEntryGroupInstance
    {
        foreach($this->selectionEntryGroups as $selectionEntryGroup) {
            if($selectionEntryGroup->getInstanceId() === $instanceId) {
                return $selectionEntryGroup;
            }
        }

        foreach($this->selectionEntries as $selectionEntry) {
            $selectionEntryGroup = $selectionEntry->findSelectionEntryGroupByInstanceId($instanceId);

            if($selectionEntryGroup !== null) {
                return $selectionEntryGroup;
            }
        }

        return null;
    }

    public function getParent(): ?TreeInterface
    {
        return $this->parent;
    }

    public function getRoot(): TreeInterface
    {
        return $this->getParent()->getRoot();
    }

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

    public function findSelectionEntryByMatcher(Closure $matcher): array
    {
        $result = [];

        foreach($this->getSelectionEntries() as $selectionEntry) {
            if($matcher($selectionEntry)) {
                $result[] = $selectionEntry;
            }

            $result += $selectionEntry->findSelectionEntryByMatcher($matcher);
        }

        foreach($this->getSelectionEntryGroups() as $selectionEntryGroup) {
            $result += $selectionEntryGroup->findSelectionEntryByMatcher($matcher);
        }

        return $result;
    }

    public function getChildren(): array
    {
        return
            $this->selectionEntryGroups +
            $this->selectionEntries;
    }

    public function getId(): string
    {
        return $this->implementation->getId();
    }

    public function getSharedId(): string
    {
        return $this->implementation->getSharedId();
    }

    public function getSelectedEntryId(): ?string
    {
        return $this->implementation->getSharedId();
    }

    public function getInstanceId(): string
    {
        return $this->instanceId;
    }

    public function getName(): string
    {
        return $this->nameOverride ?? $this->implementation->getName();
    }

    public function setName(?string $name): void
    {
        $this->nameOverride = $name;
    }

    public function isHidden(): bool
    {
        return $this->hiddenOverride ?? $this->implementation->isHidden();
    }

    public function setHidden(?bool $hidden): void
    {
        $this->hiddenOverride = $hidden;
    }

    public function isCollective(): bool
    {
        return $this->implementation->isCollective();
    }

    public function isImport(): bool
    {
        return $this->implementation->isImport();
    }

    public function isSelected(): bool
    {
        if($this->parent instanceof SelectionEntryGroupInstance) {
            return $this->parent->getSelectedEntry() === $this;
        }

        return false;
    }

    public function getType(): SelectionEntryType
    {
        return $this->implementation->getType();
    }

    public function getModifiers(): array
    {
        return $this->implementation->getModifiers();
    }

    /**
     * @return ConstraintInstance[]
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    public function findConstraint(string $id): ?ConstraintInstance
    {
        foreach($this->constraints as $constraint) {
            if($constraint->getId() === $id) {
                return $constraint;
            }
        }

        return null;
    }

    public function findCost(string $id): ?CostInstance
    {
        foreach($this->costs as $cost) {
            if($cost->getTypeId() === $id) {
                return $cost;
            }
        }

        return null;
    }

    public function getProfiles(): array
    {
        return $this->implementation->getProfiles();
    }

    public function getInfoLinks(): array
    {
        return $this->implementation->getInfoLinks();
    }

    public function getCategoryLinks(): array
    {
        return $this->implementation->getCategoryLinks();
    }

    public function getSelectionEntryGroups(): array
    {
        return $this->selectionEntryGroups;
    }

    public function getSelectionEntries(): array
    {
        return $this->selectionEntries;
    }

    public function getEntryLinks(): array
    {
        return $this->implementation->getEntryLinks();
    }

    public function getCosts(): array
    {
        return $this->implementation->getCosts();
    }

    public function getCategoryEntries(): array
    {
        return $this->implementation->getCategoryEntries();
    }
}
