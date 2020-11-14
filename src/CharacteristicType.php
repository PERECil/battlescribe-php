<?php

declare(strict_types=1);

namespace Battlescribe;

use SimpleXMLElement;

class CharacteristicType
{
    private string $id;
    private string $name;

    public function __construct(
        string $id,
        string $name
    )
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public static function fromXml(?SimpleXMLElementFacade $element): ?self
    {
        if($element === null) {
            return null;
        }

        return new self(
            $element->getAttribute('id')->asString(),
            $element->getAttribute('name')->asString()
        );
    }
}
