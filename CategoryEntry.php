<?php

declare(strict_types=1);

namespace Battlescribe;

use SimpleXMLElement;

class CategoryEntry
{
    private const NAME = 'categoryEntry';

    private string $id;
    private string $name;
    private bool $hidden;

    /** @var Modifier[] */
    private array $modifiers;

    public function __construct(
        string $id,
        string $name,
        bool $hidden
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->hidden = $hidden;

        $this->modifiers = [];
    }

    public function getId(): string
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

    public static function fromXml(?SimpleXMLElementFacade $element): ?self
    {
        if($element === null) {
            return null;
        }

        if($element->getName() !== self::NAME) {
            throw new UnexpectedNodeException( 'Received a '.$element->getName().', expected '.self::NAME);
        }

        $result = new self(
            $element->getAttribute('id')->asString(),
            $element->getAttribute('name')->asString(),
            $element->getAttribute('hidden')->asBoolean()
        );

        foreach($element->xpath('modifiers/modifier') as $modifier) {
            $result->addModifier(Modifier::fromXml($modifier));
        }

        return $result;
    }
}
