<?php

declare(strict_types=1);

namespace Battlescribe\Roster;

use Battlescribe\Data\BranchTrait;
use Battlescribe\Data\Identifier;
use Battlescribe\Data\SelectableInterface;
use Battlescribe\Data\SelectableTrait;
use Battlescribe\Data\SelectionEntryGroupInterface;
use Battlescribe\Data\SelectionEntryInterface;
use Battlescribe\Data\SelectionEntryType;
use Battlescribe\Data\TreeInterface;
use Closure;

class SelectionEntryInstance implements SelectionEntryInterface
{
    use BranchTrait;
    use SelectableTrait;

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

    /** @var ErrorInterface[] */
    private array $errors;

    public function __construct(?TreeInterface $parent, SelectionEntryInterface $implementation)
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
            $this->costs[] = new CostInstance($this, $cost);
        }

        // Needs to have instances because modifiers can modify constraints
        $this->constraints = [];

        foreach($this->implementation->getConstraints() as $constraint) {
            $this->constraints[] = new ConstraintInstance($this, $constraint);
        }

        // If there is a selection entry group as a parent, the item is selected only if it's the default selection
        if($this->parent instanceof SelectionEntryGroupInterface) {
            $this->selectedCount = $this->parent->getDefaultSelectionEntryId()?->equals($this->implementation->getSharedId()) ? 1 : 0;
        } else {
            $this->selectedCount = 1;
        }

        $this->errors = [];
    }

    public function addError(ErrorInterface $error): void
    {
        $this->errors[] = $error;
    }

    /** @return ErrorInterface[] */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param SelectionEntryInstance[] $selectionEntries
     */
    public function computeState(array $selectionEntries): void
    {
        foreach($this->constraints as $constraint) {
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

    public function getImplementation(): SelectionEntryInterface
    {
        return $this->implementation;
    }

    public function getChildren(): array
    {
        return array_merge(
            $this->selectionEntryGroups,
            $this->selectionEntries,
            $this->implementation->getRules()
        );
    }

    public function getRules(): array
    {
        return $this->implementation->getRules();
    }

    public function getId(): Identifier
    {
        return $this->implementation->getId();
    }

    public function getSharedId(): Identifier
    {
        return $this->implementation->getSharedId();
    }

    public function getSelectedEntryId(): ?Identifier
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

    public function findConstraint(Identifier $id): ?ConstraintInstance
    {
        foreach($this->constraints as $constraint) {
            if($constraint->getId()->equals($id)) {
                return $constraint;
            }
        }

        return null;
    }

    /** @inheritDoc */
    public function getProfiles(): array
    {
        return $this->implementation->getProfiles();
    }

    /** @inheritDoc */
    public function getInfoLinks(): array
    {
        return $this->implementation->getInfoLinks();
    }

    /** @inheritDoc */
    public function getCategoryLinks(): array
    {
        return $this->implementation->getCategoryLinks();
    }

    /** @inheritDoc */
    public function getSelectionEntryGroups(): array
    {
        return $this->selectionEntryGroups;
    }

    /** @inheritDoc */
    public function getSelectionEntries(): array
    {
        return $this->selectionEntries;
    }

    /** @inheritDoc */
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

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getSelections(): array
    {
        $result = [];

        foreach( $this->selectionEntries as $se) {
            if($se->getSelectedCount() > 0) {
                $result[] = $se;
                $result = array_merge($result, $se->getSelections());
            }
        }

        foreach( $this->selectionEntryGroups as $seg) {
            $result = array_merge($result, $seg->getSelections());
        }

        return $result;
    }
}
