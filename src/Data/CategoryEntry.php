<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;

class CategoryEntry implements CategoryEntryInterface
{
    use BranchTrait;

    private const NAME = 'categoryEntry';

    private Identifier $id;
    private string $name;
    private bool $hidden;

    /** @var Modifier[] */
    private array $modifiers;

    public function __construct(
        ?TreeInterface $parent,
        Identifier $id,
        string $name,
        bool $hidden
    )
    {
        $this->parent = $parent;

        $this->id = $id;
        $this->name = $name;
        $this->hidden = $hidden;

        $this->modifiers = [];
    }

    /** @inheritDoc */
    public function getChildren(): array
    {
        return array_merge(
            $this->modifiers,
        );
    }

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getSharedId(): Identifier
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function addModifier(Modifier $modifier): void
    {
        $this->modifiers[] = $modifier;
    }

    public static function fromXml(?TreeInterface $parent, ?SimpleXMLElementFacade $element): ?self
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
            $element->getAttribute('hidden')->asBoolean()
        );

        foreach($element->xpath('modifiers/modifier') as $modifier) {
            $result->addModifier(Modifier::fromXml($result, $modifier));
        }

        return $result;
    }

    public function isPrimary(): bool
    {
        return false;
    }
}
