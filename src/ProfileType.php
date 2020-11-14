<?php

declare(strict_types=1);

namespace Battlescribe;

use SimpleXMLElement;

class ProfileType
{
    private const NAME = 'profileType';

    private string $id;
    private string $name;

    /** @var CharacteristicType[] */
    private array $characteristicTypes;

    public function __construct(
        string $id,
        string $name
    )
    {
        $this->id = $id;
        $this->name = $name;

        $this->characteristicTypes = [];
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public static function fromXml(?SimpleXmlElementFacade $element): ?self
    {
        if($element === null) {
            return null;
        }

        if($element->getName() !== self::NAME) {
            throw new UnexpectedNodeException( 'Received a '.$element->getName().', expected '.self::NAME);
        }

        $result = new static(
            $element->getAttribute('id')->asString(),
            $element->getAttribute('name')->asString()
        );

        foreach($element->xpath('characteristicTypes/characteristicType') as $characteristicType) {
            $result->addCharacteristicType(CharacteristicType::fromXml($characteristicType));
        }

        return $result;
    }

    public function addCharacteristicType(?CharacteristicType $characteristicType)
    {
        $this->characteristicTypes[] = $characteristicType;
    }
}
