<?php

declare(strict_types=1);

namespace Battlescribe\Roster;

use Battlescribe\Data\Identifier;
use Battlescribe\Data\IdentifierInterface;
use Battlescribe\Data\LeafTrait;
use Battlescribe\Data\TreeInterface;
use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;

class Category implements TreeInterface, IdentifierInterface
{
    use LeafTrait;

    private const NAME = 'category';

    private Identifier $id;
    private string $name;
    private Identifier $entryId;
    private ?bool $isPrimary;

    public function __construct(
        ?TreeInterface $parent,
        Identifier $id,
        string $name,
        Identifier $entryId,
        ?bool $isPrimary
    )
    {
        $this->parent = $parent;

        $this->id = $id;
        $this->name = $name;
        $this->entryId = $entryId;
        $this->isPrimary = $isPrimary;
    }

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEntryId(): Identifier
    {
        return $this->entryId;
    }

    public function isPrimary(): ?bool
    {
        return $this->isPrimary;
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
            $element->getAttribute('entryId')->asIdentifier(),
            $element->getAttribute('isPrimary')->asBoolean(),
        );
    }
}
