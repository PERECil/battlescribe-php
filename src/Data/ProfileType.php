<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;

class ProfileType implements IdentifierInterface, TreeInterface
{
    use BranchTrait;

    private const NAME = 'profileType';

    private Identifier $id;
    private string $name;

    /** @var CharacteristicType[] */
    private array $characteristicTypes;

    public function __construct(
        ?TreeInterface $parent,
        Identifier $id,
        string $name
    )
    {
        $this->parent = $parent;

        $this->id = $id;
        $this->name = $name;

        $this->characteristicTypes = [];
    }

    /** @inheritDoc */
    public function getChildren(): array
    {
        return array_merge(
            $this->characteristicTypes,
        );
    }

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
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
            $element->getAttribute('name')->asString()
        );

        foreach($element->xpath('characteristicTypes/characteristicType') as $characteristicType) {
            $result->addCharacteristicType(CharacteristicType::fromXml($result, $characteristicType));
        }

        return $result;
    }

    public function addCharacteristicType(?CharacteristicType $characteristicType)
    {
        $this->characteristicTypes[] = $characteristicType;
    }
}
