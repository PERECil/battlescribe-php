<?php

declare(strict_types=1);

namespace Battlescribe;

use JsonSerializable;

class Characteristic implements JsonSerializable
{
    private const NAME = 'characteristic';

    private string $name;
    private string $typeId;
    private string $value;

    public function __construct(
        string $name,
        string $typeId,
        string $value
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

    public function getValue(): string
    {
        return $this->value;
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
            $element->asString()
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
