<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;
use Battlescribe\Utils\Utils;
use Closure;
use UnexpectedValueException;

class Modifier implements TreeInterface
{
    use BranchTrait;

    private const NAME = 'modifier';

    private ModifierType $type;
    private string $field;
    private string $value;

    /** @var Condition[] */
    private array $conditions;

    /** @var ConditionGroup[] */
    private array $conditionGroups;

    public function __construct(
        ?TreeInterface $parent,
        ModifierType $type,
        string $field,
        string $value
    )
    {
        $this->parent = $parent;
        $this->type = $type;
        $this->field = $field;
        $this->value = $value;

        $this->conditions = [];
        $this->conditionGroups = [];
    }

    /** @inheritDoc */
    public function getChildren(): array
    {
        return array_merge(
            $this->conditions,
            $this->conditionGroups
        );
    }

    /**
     * @param SelectionEntryInterface[] $selectionEntries
     * @param ModifiableInterface $selectionEntry
     */
    public function applyTo(array $selectionEntries, ModifiableInterface $selectionEntry): void
    {
        $isValid = true;

        foreach ($this->conditions as $condition) {
            $isValid = $isValid && $condition->isValid($selectionEntries, $selectionEntry);
        }

        foreach ($this->conditionGroups as $conditionGroup) {
            $isValid = $isValid && $conditionGroup->isValid($selectionEntries, $selectionEntry);
        }

        switch ($this->type) {
            case ModifierType::SET:

                // Battlescribe seems to store two different things in the "field" field:
                // - A battlescribe id, that links to a constraint to modify, in this case we need to modify the constraint
                // - A battlescribe id, that links to a cost to modify, in this case we need to modify the cost ?
                // - A field name, in this case we need to modify the selection entry value
                if (Utils::isId($this->field)) {

                    if (($constraint = $selectionEntry->findConstraint(new Identifier($this->field))) !== null) {
                        $constraint->setValue($isValid ? floatval($this->value) : null);
                    } elseif (($cost = $selectionEntry->findCost(new Identifier($this->field))) !== null) {
                        $cost->setValue($isValid ? floatval($this->value) : null);
                    } else {
                        throw new UnexpectedValueException("Could not find constraint with id " . $this->field);
                    }


                } else {

                    if ($this->field === 'hidden') {
                        $selectionEntry->setHidden($isValid ? ($this->value === 'true') : null);
                    } elseif ($this->field === 'name') {
                        $selectionEntry->setName($isValid ? $this->interpolateName($selectionEntry) : null);
                    } else {
                        throw new UnexpectedValueException("The modifier operation on field " . $this->field . " has not been implemented");
                    }
                }
                break;

            case ModifierType::INCREMENT:
                if (Utils::isId($this->field)) {
                    if (($constraint = $selectionEntry->findConstraint(new Identifier($this->field))) !== null) {
                        $constraint->setValue($isValid ? floatval($this->value) + $constraint->getValue() : null);
                    } elseif (($cost = $selectionEntry->findCost(new Identifier($this->field))) !== null) {
                        $cost->setValue($isValid ? floatval($this->value) + $cost->getValue() : null);
                    } else {
                        throw new UnexpectedValueException("Could not find constraint with id " . $this->field);
                    }
                } else {
                    throw new UnexpectedValueException("The modifier operation on field " . $this->field . " has not been implemented");
                }
                break;

            case ModifierType::DECREMENT:
                if (Utils::isId($this->field)) {
                    if (($constraint = $selectionEntry->findConstraint(new Identifier($this->field))) !== null) {
                        $constraint->setValue($isValid ? floatval($this->value) - $constraint->getValue() : null);
                    } elseif (($cost = $selectionEntry->findCost(new Identifier($this->field))) !== null) {
                        $cost->setValue($isValid ? floatval($this->value) - $cost->getValue() : null);
                    } else {
                        throw new UnexpectedValueException("Could not find constraint with id " . $this->field);
                    }
                } else {
                    throw new UnexpectedValueException("The modifier operation on field " . $this->field . " has not been implemented");
                }
                break;

            default:
                throw new UnexpectedValueException("The modifier operation " . $this->type . " has not been implemented");
        }
    }

    private function interpolateName(ModifiableInterface $selectionEntry): string
    {
        $value = $this->value;

        // Special case for %x values that indicate a count
        if( strpos( $value, '1x ' ) === 0 ) {
            $value = str_replace( '1x ', $selectionEntry->getSelectedCount() . 'x ', $value );
        }

        return $value;
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

    public static function fromXml(?TreeInterface $parent, ?SimpleXMLElementFacade $element): ?self
    {
        if ($element === null) {
            return null;
        }

        if ($element->getName() !== self::NAME) {
            throw new UnexpectedNodeException('Received a ' . $element->getName() . ', expected ' . self::NAME);
        }

        $result = new self(
            $parent,
            $element->getAttribute('type')->asEnum(ModifierType::class),
            $element->getAttribute('field')->asString(),
            $element->getAttribute('value')->asString()
        );

        foreach ($element->xpath('conditions/condition') as $condition) {
            $result->addCondition(Condition::fromXml($result, $condition));
        }

        foreach ($element->xpath('conditionGroups/conditionGroup') as $conditionGroup) {
            $result->addConditionGroup(ConditionGroup::fromXml($result, $conditionGroup));
        }

        // TODO Handle <repeats>

        return $result;
    }
}
