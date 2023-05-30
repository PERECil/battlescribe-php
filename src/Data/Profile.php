<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;
use Closure;

class Profile implements ProfileInterface
{
    use BranchTrait;

    private const NAME = 'profile';

    private Identifier $id;
    private string $name;
    private ?Identifier $publicationId;
    private bool $hidden;
    private ?string $typeId;
    private ?string $typeName;

    /** @var Characteristic[] */
    private array $characteristics;

    public function __construct(
        ?TreeInterface $parent,
        Identifier $id,
        string $name,
        ?Identifier $publicationId,
        bool $hidden,
        ?string $typeId,
        ?string $typeName
    )
    {
        $this->parent = $parent;

        $this->id = $id;
        $this->name = $name;
        $this->publicationId = $publicationId;
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

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPublicationId(): ?Identifier
    {
        return $this->publicationId;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function getTypeId(): ?string
    {
        return $this->typeId;
    }

    public function getTypeName(): ?string
    {
        return $this->typeName;
    }

    public static function fromXml(?TreeInterface $parent, ?SimpleXmlElementFacade $element): ?self
    {
        if($element === null) {
            return null;
        }

        if($element->getName() !== self::NAME) {
            throw new UnexpectedNodeException( 'Received a '.$element->getName().', expected '.self::NAME);
        }

        $result = new static(
            $parent,
            $element->getAttribute('id')->asIdentifier(),
            $element->getAttribute('name')->asString(),
            $element->getAttribute('publicationId')->asIdentifier(),
            $element->getAttribute('hidden')->asBoolean(),
            $element->getAttribute('typeId')->asString(),
            $element->getAttribute('typeName')->asString()
        );

        foreach($element->xpath('characteristics/characteristic') as $characteristic) {
            $result->addCharacteristic(Characteristic::fromXml($result, $characteristic));
        }

        return $result;
    }

    /** @inheritDoc */
    public function getChildren(): array
    {
        return array_merge(
            $this->characteristics
        );
    }
}
