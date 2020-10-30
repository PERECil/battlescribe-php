<?php

declare(strict_types=1);

namespace Battlescribe;

use JsonSerializable;

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

    public function addCharacteristic(Characteristic $characteristic): void
    {
        $this->characteristics[] = $characteristic;
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

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'characteristics' => $this->characteristics,
            'type_id' => $this->typeId,
            'type_name' => $this->typeName,
        ];
    }
}
