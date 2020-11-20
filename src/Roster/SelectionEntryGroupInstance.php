<?php

declare(strict_types=1);

namespace Battlescribe\Roster;

use Battlescribe\Data\SelectionEntryGroupInterface;
use Battlescribe\Data\SelectionEntryInterface;
use Battlescribe\Data\TreeInterface;
use Closure;
use UnexpectedValueException;

class SelectionEntryGroupInstance implements SelectionEntryGroupInterface
{
    private TreeInterface $parent;
    private string $instanceId;
    private ?string $selectedInstanceId;
    private SelectionEntryGroupInterface $implementation;
    private ?bool $hiddenOverride;

    /** @var SelectionEntryInstance[] */
    private array $selectionEntries;

    /** @var SelectionEntryGroupInstance[] */
    private array $selectionEntryGroups;

    /** @var ConstraintInstance[] */
    private array $constraints;

    public function __construct(TreeInterface $parent, SelectionEntryGroupInterface $implementation)
    {
        $this->parent = $parent;
        $this->instanceId = spl_object_hash($this);
        $this->selectedInstanceId = null;
        $this->implementation = $implementation;
        $this->hiddenOverride = null;

        $this->selectionEntries = [];

        foreach($this->implementation->getSelectionEntries() as $selectionEntry) {

            $instance = new SelectionEntryInstance($this, $selectionEntry);

            $this->selectionEntries[] = $instance;

            if($selectionEntry->getId() === $this->getDefaultSelectionEntryId()) {
                $this->selectedInstanceId = $instance->getInstanceId();
            }
        }

        $this->selectionEntryGroups = [];

        foreach($this->implementation->getSelectionEntryGroups() as $selectionEntryGroup) {

            $instance =  new SelectionEntryGroupInstance($this, $selectionEntryGroup);

            $this->selectionEntryGroups[] = $instance;

            if($selectionEntryGroup->getId() === $this->getDefaultSelectionEntryId()) {
                $this->selectedInstanceId = $instance->getInstanceId();
            }
        }

        // Needs to have instances because modifiers can modify constraints
        $this->constraints = [];

        foreach($this->implementation->getConstraints() as $constraint) {
            $this->constraints[] = new ConstraintInstance($constraint);
        }
    }

    public function computeState(array $selectionEntries): void
    {
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

    public function getInstanceId(): string
    {
        return $this->instanceId;
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
        $selectedEntry = $this->getSelectedEntry();

        if($selectedEntry !== null ) {
            return $selectedEntry->getId();
        }

        return null;
    }

    public function setSelectedInstanceId(string $instanceId): void
    {
        foreach($this->selectionEntries as $selectionEntry) {
            if($selectionEntry->getInstanceId() === $instanceId) {
                $this->selectedInstanceId = $instanceId;

                $this->getRoot()->computeState();

                return;
            }
        }

        throw new UnexpectedValueException("Instance id not found");
    }

    public function getRoot(): TreeInterface
    {
        return $this->getParent()->getRoot();
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

    public function getSelectedEntry(): ?SelectionEntryInstance
    {
        if($this->selectedInstanceId === null) {
            return null;
        }

        foreach($this->selectionEntries as $selectionEntry) {
            if($selectionEntry->getInstanceId() === $this->selectedInstanceId) {
                return $selectionEntry;
            }
        }

        throw new UnexpectedValueException('No selection entry instance found with instance id of '.$this->selectedInstanceId);
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

    public function getChildren(): array
    {
        return
            $this->selectionEntries +
            $this->selectionEntryGroups;
    }

    public function getName(): string
    {
        return $this->implementation->getName();
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

    public function getDefaultSelectionEntryId(): ?string
    {
        return $this->implementation->getDefaultSelectionEntryId();
    }

    public function getModifiers(): array
    {
        return $this->implementation->getModifiers();
    }

    public function getConstraints(): array
    {
        return $this->implementation->getConstraints();
    }

    public function getSelectionEntries(): array
    {
        return $this->selectionEntries;
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

    public function findSelectionEntryByName(string $name): array
    {
        $result = [];

        foreach($this->selectionEntries as $selectionEntry) {
            if($selectionEntry->getName() === $name) {
                $result[] = $selectionEntry;
            }
        }

        return $result;
    }

    public function getSelectionEntryGroups(): array
    {
        return $this->selectionEntryGroups;
    }

    public function getEntryLinks(): array
    {
        return $this->implementation->getEntryLinks();
    }
}
