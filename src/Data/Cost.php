<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;
use Exception;

class Cost implements CostInterface
{
    use LeafTrait;

    private const NAME = 'cost';

    private string $name;
    private ?Identifier $typeId;
    private float $value;

    private ?float $valueOverride;

    public function __construct(
        ?TreeInterface $parent,
        string $name,
        ?Identifier $typeId,
        float $value
    )
    {
        $this->parent = $parent;

        $this->name = $name;
        $this->typeId = $typeId;
        $this->value = $value;

        $this->valueOverride = null;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTypeId(): ?Identifier
    {
        return $this->typeId;
    }

    public function setValue(?float $value): void
    {
        $this->valueOverride = $value;
    }

    public function getValue(): float
    {
        return $this->valueOverride ?? $this->value;
    }

    public function isFree(): bool
    {
        return abs($this->value) < 0.001;
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
            $element->getAttribute('typeId')->asIdentifier(),
            $element->getAttribute('value')->asFloat(),
        );
    }
}
