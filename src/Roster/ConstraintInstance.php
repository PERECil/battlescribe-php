<?php

declare(strict_types=1);

namespace Battlescribe\Roster;

use Battlescribe\Data\ConstraintInterface;
use Battlescribe\Data\ConstraintType;
use Battlescribe\Data\ModifiableInterface;
use Battlescribe\Data\SelectionEntryInterface;

class ConstraintInstance implements ConstraintInterface
{
    private ?float $valueOverride = null;

    private ConstraintInterface $implementation;
    private string $instanceId;

    public function __construct(ConstraintInterface $implementation)
    {
        $this->implementation = $implementation;
        $this->instanceId = spl_object_hash($this);
    }

    public function applyTo(array $selectionEntries, ModifiableInterface $selectionEntry): void
    {
        $isValid = true;

        foreach($this->getConditions() as $condition) {
            $isValid = $isValid && $condition->isValid($selectionEntries, $selectionEntry);
        }

        foreach($this->getConditionGroups() as $conditionGroup) {
            $isValid = $isValid && $conditionGroup->isValid($selectionEntries, $selectionEntry);
        }

        if($isValid) {
            if(ConstraintType::MIN()->equals($this->getType())) {
                $selectionEntry->setMinimumSelectedCount((int)$this->getValue());

                switch($this->getScope()) {
                    case 'force':
                        $entries = $selectionEntry->getRoot()->findSelectionEntryByMatcher(function(SelectionEntryInterface $e) use($selectionEntry) { return $selectionEntry->getSharedId() === $e->getSharedId();});

                        $totalSelectedCount = array_reduce($entries, function(SelectionEntryInterface $e) { return $e->getSelectedCount(); }, 0);

                        if($totalSelectedCount < $selectionEntry->getMinimumSelectedCount()) {
                            $selectionEntry->addError(new ConstraintError($this));
                        }

                        break;
                    case 'parent':
                        if($selectionEntry->getSelectedCount() < $selectionEntry->getMinimumSelectedCount()) {
                            $selectionEntry->addError(new ConstraintError($this));
                        }

                        break;
                    default:
                        throw new \UnexpectedValueException('Scope '.$this->scope.' not handled in constraint' );
                }
            }

            if(ConstraintType::MAX()->equals($this->getType())) {
                $selectionEntry->setMaximumSelectedCount((int)$this->getValue());

                switch($this->implementation->getScope()) {
                    case 'force':
                        $entries = $selectionEntry->getRoot()->findSelectionEntryByMatcher(function(SelectionEntryInterface $e) use($selectionEntry) { return $selectionEntry->getSharedId() === $e->getSharedId();});

                        $totalSelectedCount = array_reduce($entries, function(int $carry, SelectionEntryInterface $e) { return $carry + $e->getSelectedCount(); }, 0);

                        if($totalSelectedCount < $selectionEntry->getMaximumSelectedCount()) {
                            $selectionEntry->addError(new ConstraintError($this));
                        }

                        break;
                    case 'parent':
                        if($selectionEntry->getSelectedCount() < $selectionEntry->getMaximumSelectedCount()) {
                            $selectionEntry->addError(new ConstraintError($this));
                        }

                        break;
                    default:
                        throw new \UnexpectedValueException('Scope '.$this->getScope().' not handled in constraint' );
                }
            }
        }
    }

    public function getInstanceId(): string
    {
        return $this->instanceId;
    }

    public function setValue(?float $value): void
    {
        $this->valueOverride = $value;
    }

    public function getValue(): float
    {
        return $this->valueOverride ?? $this->implementation->getValue();
    }

    public function getId(): string
    {
        return $this->implementation->getId();
    }

    public function getField(): string
    {
        return $this->implementation->getField();
    }

    public function getScope(): string
    {
        return $this->implementation->getScope();
    }

    public function isPercentValue(): bool
    {
        return $this->implementation->isPercentValue();
    }

    public function isShared(): bool
    {
        return $this->implementation->isShared();
    }

    public function includeChildSelections(): bool
    {
        return $this->implementation->includeChildSelections();
    }

    public function includeChildForces(): bool
    {
        return $this->implementation->includeChildForces();
    }

    public function getType(): ConstraintType
    {
        return $this->implementation->getType();
    }

    public function getConditions(): array
    {
        return $this->implementation->getConditions();
    }

    public function getConditionGroups(): array
    {
        return $this->implementation->getConditionGroups();
    }
}
