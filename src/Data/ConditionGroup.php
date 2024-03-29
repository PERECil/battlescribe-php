<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;
use UnexpectedValueException;

class ConditionGroup implements TreeInterface
{
    use BranchTrait;

    private const NAME = 'conditionGroup';

    private ConditionGroupType $type;

    /** @var ConditionGroup[] */
    private array $conditionGroups;

    /** @var Condition[] */
    private array $conditions;

    public function __construct(?TreeInterface $parent, ConditionGroupType $type)
    {
        $this->parent = $parent;
        $this->type = $type;

        $this->conditionGroups = [];
        $this->conditions = [];
    }

    /** @inheritDoc */
    public function getChildren(): array
    {
        return array_merge(
            $this->conditionGroups,
            $this->conditions
        );
    }

    public function isValid(array $selectionEntries, ModifiableInterface $selectionEntry): bool
    {
        switch($this->type) {
            case ConditionGroupType::AND:
                $isValid = true;

                foreach($this->conditionGroups as $conditionGroup) {
                    $isValid = $isValid && $conditionGroup->isValid($selectionEntries, $selectionEntry);
                }

                foreach($this->conditions as $condition) {
                    $isValid = $isValid && $condition->isValid($selectionEntries, $selectionEntry);
                }

                return $isValid;

            case ConditionGroupType::OR:
                $isValid = false;

                foreach($this->conditionGroups as $conditionGroup) {
                    $isValid = $isValid || $conditionGroup->isValid($selectionEntries, $selectionEntry);
                }

                foreach($this->conditions as $condition) {
                    $isValid = $isValid || $condition->isValid($selectionEntries, $selectionEntry);
                }

                return $isValid;

            default:
                throw new UnexpectedValueException("The condition group type ".$this->type." has not been implemented");
        }
    }

    public function getType(): ConditionGroupType
    {
        return $this->type;
    }

    public function addConditionGroup(ConditionGroup $conditionGroup): void
    {
        $this->conditionGroups[] = $conditionGroup;
    }

    public function addCondition(Condition $condition): void
    {
        $this->conditions[] = $condition;
    }

    public static function fromXml(?TreeInterface $parent, ?SimpleXMLElementFacade $element): ?self
    {
        if($element === null) {
            return null;
        }

        if($element->getName() !== self::NAME) {
            throw new UnexpectedNodeException( 'Received a '.$element->getName().', expected '.self::NAME);
        }

        $result = new self(
            $parent,
            $element->getAttribute('type')->asEnum(ConditionGroupType::class)
        );

        foreach($element->xpath('conditionGroups/conditionGroup') as $conditionGroup) {
            $result->addConditionGroup(ConditionGroup::fromXml($result, $conditionGroup));
        }

        foreach($element->xpath('conditions/condition') as $condition) {
            $result->addCondition(Condition::fromXml($result, $condition));
        }

        return $result;
    }
}
