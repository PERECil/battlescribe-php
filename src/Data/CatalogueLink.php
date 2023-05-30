<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;

class CatalogueLink implements IdentifierInterface, TreeInterface
{
    use LeafTrait;

    private const NAME = 'catalogueLink';

    private Identifier $id;
    private string $name;
    private Identifier $targetId;
    private CatalogueLinkType $type;
    private bool $importRootEntries;

    public function __construct(
        ?TreeInterface $parent,
        Identifier $id,
        string $name,
        Identifier $targetId,
        CatalogueLinkType $type,
        bool $importRootEntries
    )
    {
        $this->parent = $parent;

        $this->id = $id;
        $this->name = $name;
        $this->targetId = $targetId;
        $this->type = $type;
        $this->importRootEntries = $importRootEntries;
    }

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getTargetId(): Identifier
    {
        return $this->targetId;
    }

    public static function fromXml(?TreeInterface $parent, ?SimpleXMLElementFacade $element): ?self
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
            $element->getAttribute('targetId')->asIdentifier(),
            $element->getAttribute('type')->asEnum(CatalogueLinkType::class),
            $element->getAttribute('importRootEntries')->asBoolean(),
        );
    }
}
