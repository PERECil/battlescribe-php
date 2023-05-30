<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;

class Characteristic implements TreeInterface, NameInterface
{
    use LeafTrait;

    private const NAME = 'characteristic';

    private string $name;
    private ?string $typeId;
    private string $value;

    public function __construct(
        ?TreeInterface $parent,
        string $name,
        ?string $typeId,
        string $value
    )
    {
        $this->parent = $parent;
        $this->name = $name;
        $this->typeId = $typeId;
        $this->value = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTypeId(): ?string
    {
        return $this->typeId;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public static function fromXml(?TreeInterface $parent, ?SimpleXmlElementFacade $element): ?self
    {
        if($element === null) {
            return null;
        }

        if($element->getName() !== self::NAME) {
            throw new UnexpectedNodeException( 'Received a '.$element->getName().', expected '.self::NAME);
        }

        return new self(
            $parent,
            $element->getAttribute('name')->asString(),
            $element->getAttribute('typeId')->asString(),
            $element->asString()
        );
    }
}
