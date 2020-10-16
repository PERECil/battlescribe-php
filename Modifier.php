<?php

declare(strict_types=1);

namespace Battlescribe;

use SimpleXMLElement;

class Modifier
{
    private const NAME = 'modifier';

    private ModifierType $type;
    private string $field;
    private string $value;

    /** @var ConditionGroup[] */
    private array $conditionGroups;

    public function __construct(ModifierType $type, string $field, string $value)
    {
        $this->type = $type;
        $this->field = $field;
        $this->value = $value;

        $this->conditionGroups = [];
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

        foreach($element->xpath('conditionGroups/conditionGroup') as $conditionGroup) {
            $result->addConditionGroup(ConditionGroup::fromXml($conditionGroup));
        }

        return $result;
    }
}
