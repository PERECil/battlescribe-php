<?php

declare(strict_types=1);

namespace Battlescribe;

use JsonSerializable;

class Cost implements CostInterface, JsonSerializable
{
    private const NAME = 'cost';

    private string $name;
    private string $typeId;
    private float $value;

    public function __construct(
        string $name,
        string $typeId,
        float $value
    )
    {
        $this->name = $name;
        $this->typeId = $typeId;
        $this->value = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTypeId(): string
    {
        return $this->typeId;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function isFree(): bool
    {
        return abs($this->value) < 0.001;
    }

    public static function fromXml(?SimpleXmlElementFacade $element): ?self
    {
        if($element === null) {
            return null;
        }

        if($element->getName() !== self::NAME) {
            throw new UnexpectedNodeException( 'Received a '.$element->getName().', expected '.self::NAME);
        }

        return new self(
            $element->getAttribute('name')->asString(),
            $element->getAttribute('typeId')->asString(),
            $element->getAttribute('value')->asFloat(),
        );
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'value' => $this->value,
        ];
    }
}
