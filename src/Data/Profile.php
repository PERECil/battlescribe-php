<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;

class Profile implements ProfileInterface
{
    private const NAME = 'profile';

    private string $id;
    private string $name;
    private bool $hidden;
    private string $typeId;
    private string $typeName;

    /** @var Characteristic[] */
    private array $characteristics;

    public function __construct(
        string $id,
        string $name,
        bool $hidden,
        string $typeId,
        string $typeName
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->hidden = $hidden;
        $this->typeId = $typeId;
        $this->typeName = $typeName;
    }

    public function getCharacteristics(): array
    {
        return $this->characteristics;
    }

    public function addCharacteristic(Characteristic $characteristic): void
    {
        $this->characteristics[] = $characteristic;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function getTypeId(): string
    {
        return $this->typeId;
    }

    public function getTypeName(): string
    {
        return $this->typeName;
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
            $element->getAttribute('name')->asString(),
            $element->getAttribute('hidden')->asBoolean(),
            $element->getAttribute('typeId')->asString(),
            $element->getAttribute('typeName')->asString()
        );

        foreach($element->xpath('characteristics/characteristic') as $characteristic) {
            $result->addCharacteristic(Characteristic::fromXml($characteristic));
        }

        return $result;
    }
}
