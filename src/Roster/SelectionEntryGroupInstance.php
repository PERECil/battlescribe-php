<?php

declare(strict_types=1);

namespace Battlescribe\Roster;

use Battlescribe\Data\BranchTrait;
use Battlescribe\Data\ConstraintInterface;
use Battlescribe\Data\Identifier;
use Battlescribe\Data\SelectionEntryGroupInterface;
use Battlescribe\Data\SelectionEntryInterface;
use Battlescribe\Data\TreeInterface;
use Closure;
use Exception;
use UnexpectedValueException;

class SelectionEntryGroupInstance implements SelectionEntryGroupInterface
{
    use BranchTrait;

    private string $instanceId;
    private SelectionEntryGroupInterface $implementation;
    private ?bool $hiddenOverride;

    /** @var SelectionEntryInstance[] */
    private array $selectionEntries;

    /** @var SelectionEntryGroupInstance[] */
    private array $selectionEntryGroups;

    /** @var ConstraintInstance[] */
    private array $constraints;

    public function __construct(?TreeInterface $parent, SelectionEntryGroupInterface $implementation)
    {
        $this->parent = $parent;

        $this->instanceId = spl_object_hash($this);
        $this->implementation = $implementation;
        $this->hiddenOverride = null;

        $this->selectionEntries = [];

        foreach($this->implementation->getSelectionEntries() as $selectionEntry) {

            $instance = new SelectionEntryInstance($this, $selectionEntry);

            $this->selectionEntries[] = $instance;

            if($this->getDefaultSelectionEntryId()?->equals($selectionEntry->getId())) {
                $instance->setSelectedCount(1);
            } else {
                $instance->setSelectedCount(0);
            }
        }

        $this->selectionEntryGroups = [];

        foreach($this->implementation->getSelectionEntryGroups() as $selectionEntryGroup) {

            $instance =  new SelectionEntryGroupInstance($this, $selectionEntryGroup);

            $this->selectionEntryGroups[] = $instance;

            /* Not sure if used
            if($selectionEntryGroup->getId() === $this->getDefaultSelectionEntryId()) {
                $instance->setSelectedCount(1);
            } else {
                $instance->setSelectedCount(0);
            }
            */
        }

        // Need to have instances because modifiers can modify constraints
        $this->constraints = [];

        foreach($this->implementation->getConstraints() as $constraint) {
            $this->constraints[] = new ConstraintInstance($this, $constraint);
        }
    }

    public function getSelectedEntry(): ?SelectionEntryInterface
    {
        $result = null;

        foreach($this->selectionEntries as $selectionEntry) {
            if($selectionEntry->getSelectedCount() > 0) {
                if($result !== null) {
                    throw new Exception('Multiple selected entries found');
                }

                $result = $selectionEntry;
            }
        }

        return $result;
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

    public function getId(): Identifier
    {
        return $this->implementation->getId();
    }

    public function getSharedId(): Identifier
    {
        return $this->implementation->getSharedId();
    }

    public function setSelectedInstanceId(string $instanceId): void
    {
        $found = false;

        foreach($this->selectionEntries as $selectionEntry) {
            if($selectionEntry->getInstanceId() === $instanceId) {
                $selectionEntry->setSelectedCount(1);
                $found = true;
            } else {
                $selectionEntry->setSelectedCount(0);
            }
        }

        if($found) {
            $this->getRoot()->computeState();
        } else {
            throw new UnexpectedValueException("Instance id not found");
        }
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
        $this->hiddenOverride = $hidden ?? $this->hiddenOverride;
    }

    public function isCollective(): bool
    {
        return $this->implementation->isCollective();
    }

    public function isImport(): bool
    {
        return $this->implementation->isImport();
    }

    public function getDefaultSelectionEntryId(): ?Identifier
    {
        return $this->implementation->getDefaultSelectionEntryId();
    }

    /** @inheritDoc */
    public function getModifiers(): array
    {
        return $this->implementation->getModifiers();
    }

    /** @inheritDoc */
    public function getConstraints(): array
    {
        return $this->implementation->getConstraints();
    }

    /** @inheritDoc */
    public function getSelectionEntries(): array
    {
        return $this->selectionEntries;
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
