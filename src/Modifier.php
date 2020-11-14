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

    public function __construct(
        ModifierType $type,
        string $field,
        string $value
    )
    {
        $this->type = $type;
        $this->field = $field;
        $this->value = $value;

        $this->conditions = [];
        $this->conditionGroups = [];
    }

    /**
     * @param SelectionEntryInterface[] $selectionEntries
     * @param SelectionEntryInterface $selectionEntry
     */
    public function applyTo(array $selectionEntries, ModifiableInterface $selectionEntry): void
    {
        $isValid = true;

        foreach($this->conditions as $condition) {
            $isValid = $isValid && $condition->isValid($selectionEntries, $selectionEntry);
        }

        foreach($this->conditionGroups as $conditionGroup) {
            $isValid = $isValid && $conditionGroup->isValid($selectionEntries, $selectionEntry);
        }

        if(!$isValid) {
            return;
        }

        switch($this->type) {
            case ModifierType::SET:

                // Battlescribe seems to store two different things in the "field" field:
                // - A battlescribe id, that links to a constraint to modify, in this case we need to modify the constraint
                // - A battlescribe id, that links to a cost to modify, in this case we need to modify the cost ?
                // - A field name, in this case we need to modify the selection entry value
                if(Utils::isId($this->field)) {

                    if( ($constraint = $selectionEntry->findConstraint($this->field)) !== null ) {
                        $constraint->setValue(floatval($this->value));
                    } elseif( ($cost = $selectionEntry->findCost($this->field)) !== null ) {
                        $cost->setValue(floatval($this->value));
                    } else {
                        throw new UnexpectedValueException( "Could not find constraint with id ".$this->field );
                    }


                } else {

                    if($this->field === 'hidden') {
                        $selectionEntry->setHidden($this->value === 'true' ? true : false);
                    } elseif($this->field === 'name') {
                        $selectionEntry->setName($this->value);
                    } else {
                        throw new UnexpectedValueException("The modifier operation on field ".$this->field." has not been implemented");
                    }
                }
                break;
            default:
                throw new UnexpectedValueException("The modifier operation ".$this->type." has not been implemented");
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

        // TODO Handle <repeats>

        return $result;
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type,
            'field' => $this->field,
            'value' => $this->value,
            'condition_groups' => $this->conditionGroups,
        ];
    }
}
