<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Utils\SimpleXmlElementFacade;

class CharacteristicType implements IdentifierInterface, TreeInterface
{
    use LeafTrait;

    private Identifier $id;
    private string $name;

    public function __construct(
        ?TreeInterface $parent,
        Identifier $id,
        string $name
    )
    {
        $this->parent = $parent;

        $this->id = $id;
        $this->name = $name;
    }

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public static function fromXml(?TreeInterface $parent, ?SimpleXMLElementFacade $element): ?self
    {
        if($element === null) {
            return null;
        }

        return new self(
            $parent,
            $element->getAttribute('id')->asIdentifier(),
            $element->getAttribute('name')->asString()
        );
    }
}
