<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;

class CostType
{
    private const NAME = 'costType';

    private string $id;
    private string $name;
    private float $defaultCostLimit;
    private bool $hidden;

    public function __construct(
        string $id,
        string $name,
        float $defaultCostLimit,
        bool $hidden
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->defaultCostLimit = $defaultCostLimit;
        $this->hidden = $hidden;
    }

    public static function fromXml(?SimpleXmlElementFacade $element): ?self
    {
        if($element === null) {
            return null;
        }

        if($element->getName() !== self::NAME) {
            throw new UnexpectedNodeException( 'Received a '.$element->getName().', expected '.self::NAME);
        }

        return new self(
            $element->getAttribute('id')->asString(),
            $element->getAttribute('name')->asString(),
            $element->getAttribute('defaultCostLimit')->asFloat(),
            $element->getAttribute('hidden')->asBoolean(),
        );
    }
}
