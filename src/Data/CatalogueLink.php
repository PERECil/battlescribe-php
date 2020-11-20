<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;

class CatalogueLink
{
    private const NAME = 'catalogueLink';

    private string $id;
    private string $name;
    private string $targetId;
    private CatalogueLinkType $type;
    private bool $importRootEntries;

    public function __construct(
        string $id,
        string $name,
        string $targetId,
        CatalogueLinkType $type,
        bool $importRootEntries
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->targetId = $targetId;
        $this->type = $type;
        $this->importRootEntries = $importRootEntries;
    }

    public static function fromXml(?SimpleXMLElementFacade $element): ?self
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
            $element->getAttribute('targetId')->asString(),
            $element->getAttribute('type')->asEnum(CatalogueLinkType::class),
            $element->getAttribute('importRootEntries')->asBoolean(),
        );
    }
}
