<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;

class CostType implements IdentifierInterface, TreeInterface
{
    use LeafTrait;

    private const NAME = 'costType';

    private Identifier $id;
    private string $name;
    private float $defaultCostLimit;
    private ?bool $hidden;

    public function __construct(
        ?TreeInterface $parent,
        Identifier $id,
        string $name,
        float $defaultCostLimit,
        ?bool $hidden
    )
    {
        $this->parent = $parent;
        $this->id = $id;
        $this->name = $name;
        $this->defaultCostLimit = $defaultCostLimit;
        $this->hidden = $hidden;
    }

    public function getId(): Identifier
    {
        return $this->id;
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
            $element->getAttribute('id')->asIdentifier(),
            $element->getAttribute('name')->asString(),
            $element->getAttribute('defaultCostLimit')->asFloat(),
            $element->getAttribute('hidden')->asBoolean(),
        );
    }
}
