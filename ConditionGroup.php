<?php

declare(strict_types=1);

namespace Battlescribe;

use JsonSerializable;
use SimpleXMLElement;

class ConditionGroup implements JsonSerializable
{
    private ConditionGroupType $type;

    /** @var ConditionGroup[] */
    private array $conditionGroups;

    /** @var Condition[] */
    private array $conditions;

    public function __construct(ConditionGroupType $type)
    {
        $this->type = $type;

        $this->conditionGroups = [];
        $this->conditions = [];
    }

    public function isValid(): bool
    {
        switch($this->type) {
            case ConditionGroupType::AND:
                $isValid = true;

                foreach($this->conditionGroups as $conditionGroup) {
                    $isValid = $isValid && $conditionGroup->isValid();
                }

                foreach($this->conditions as $condition) {
                    $isValid = $isValid && $condition->isValid();
                }

                return $isValid;

            case ConditionGroupType::OR:
                $isValid = false;

                foreach($this->conditionGroups as $conditionGroup) {
                    $isValid = $isValid || $conditionGroup->isValid();
                }

                foreach($this->conditions as $condition) {
                    $isValid = $isValid || $condition->isValid();
                }

                return $isValid;

            default:
                throw new \UnexpectedValueException("The condition group type ".$this->type." has not been implemented");
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

    public static function fromXml(?SimpleXMLElementFacade $element): ?self
    {
        if($element === null) {
            return null;
        }

        $result = new self(
            $element->getAttribute('type')->asEnum(ConditionGroupType::class)
        );

        foreach($element->xpath('conditionGroups/conditionGroup') as $conditionGroup) {
            $result->addConditionGroup(ConditionGroup::fromXml($conditionGroup));
        }

        foreach($element->xpath('conditions/condition') as $condition) {
            $result->addCondition(Condition::fromXml($condition));
        }

        return $result;
    }

    public function jsonSerialize()
    {
        return [
            'type' => $this->type,
            'condition_groups' => $this->conditionGroups,
            'conditions' => $this->conditions,
        ];
    }
}
