<?php

declare(strict_types=1);

namespace Battlescribe;

use JsonSerializable;
use ReflectionProperty;
use SimpleXMLElement;
use UnexpectedValueException;

class Modifier implements JsonSerializable
{
    private const NAME = 'modifier';

    private ModifierType $type;
    private string $field;
    private string $value;

    /** @var Condition[] */
    private array $conditions;

    /** @var ConditionGroup[] */
    private array $conditionGroups;

    public function __construct(ModifierType $type, string $field, string $value)
    {
        $this->type = $type;
        $this->field = $field;
        $this->value = $value;

        $this->conditions = [];
        $this->conditionGroups = [];
    }

    public function applyTo(SelectionEntryInterface $selectionEntry): void
    {
        $isValid = true;

        foreach($this->conditions as $condition) {
            $isValid = $isValid && $condition->isValid();
        }

        foreach($this->conditionGroups as $conditionGroup) {
            $isValid = $isValid && $conditionGroup->isValid();
        }

        if(!$isValid) {
            return;
        }

        switch($this->type) {
            case ModifierType::SET:

                // Battlescribe seems to store two different things in the "field" field:
                // - A battlescribe id, that links to a constraint to modify, in this case we need to modify the constraint
                // - A field name, in this case we need to modify the selection entry value
                if(Utils::isId($this->field)) {
                    $constraint = $selectionEntry->findConstraint($this->field);

                    if( $constraint === null ) {
                        throw new UnexpectedValueException( "Could not find constraint with id ".$this->field );
                    }

                    $constraint->setValue(floatval($this->value));
                } else {
                    $property = new ReflectionProperty($selectionEntry, $this->field);
                    $property->setAccessible(true);
                    $property->setValue($selectionEntry, $this->value);
                }
                break;
            default:
                throw new \UnexpectedValueException("The modifier operation ".$this->type." has not been implemented");
        }
    }

    public function getType(): ModifierType
    {
        return $this->type;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function addCondition(Condition $condition): void
    {
        $this->conditions[] = $condition;
    }

    public function addConditionGroup(ConditionGroup $conditionGroup): void
    {
        $this->conditionGroups[] = $conditionGroup;
    }

    public static function fromXml(?SimpleXMLElementFacade $element): ?self
    {
        if($element === null) {
            return null;
        }

        if($element->getName() !== self::NAME) {
            throw new UnexpectedNodeException( 'Received a '.$element->getName().', expected '.self::NAME);
        }

        $result = new self(
            $element->getAttribute('type')->asEnum(ModifierType::class),
            $element->getAttribute('field')->asString(),
            $element->getAttribute('value')->asString()
        );

        foreach($element->xpath('conditions/condition') as $condition) {
            $result->addCondition(Condition::fromXml($condition));
        }

        foreach($element->xpath('conditionGroups/conditionGroup') as $conditionGroup) {
            $result->addConditionGroup(ConditionGroup::fromXml($conditionGroup));
        }

        return $result;
    }

    public function jsonSerialize()
    {
        return [
            'type' => $this->type,
            'field' => $this->field,
            'value' => $this->value,
            'condition_groups' => $this->conditionGroups,
        ];
    }
}
