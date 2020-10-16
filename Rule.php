<?php

declare(strict_types=1);

namespace Battlescribe;

use SimpleXMLElement;

class Rule
{
    private const NAME = 'rule';

    private string $id;
    private string $name;
    private bool $hidden;
    private ?string $description;

    /** @var Modifier[] */
    private array $modifiers;

    public function __construct(
        string $id,
        string $name,
        bool $hidden,
        ?string $description
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->hidden = $hidden;
        $this->description = $description;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function addModifier(Modifier $modifier): void
    {
        $this->modifiers[] = $modifier;
    }

    public static function fromXml(?SimpleXmlElementFacade $element): ?self
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
            $element->getAttribute('hidden')->asBoolean(),
            $element->xpath('description')->current()->asString()
        );

        foreach($element->xpath('modifiers/modifier') as $modifier) {
            $result->addModifier(Modifier::fromXml($modifier));
        }

        return $result;
    }
}
