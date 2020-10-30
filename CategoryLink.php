<?php

declare(strict_types=1);

namespace Battlescribe;

use JsonSerializable;

class CategoryLink implements JsonSerializable
{
    private const NAME = 'categoryLink';

    private string $id;
    private string $name;
    private bool $hidden;
    private string $targetId;
    private bool $primary;

    /** @var Modifier[] */
    private array $modifiers;

    /** @var Constraint[] */
    private array $constraints;

    public function __construct(
        string $id,
        string $name,
        bool $hidden,
        string $targetId,
        bool $primary
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->hidden = $hidden;
        $this->targetId = $targetId;
        $this->primary = $primary;

        $this->modifiers = [];
        $this->constraints = [];
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

    public function getTargetId(): string
    {
        return $this->targetId;
    }

    public function isPrimary(): bool
    {
        return $this->primary;
    }

    public function getModifiers(): array
    {
        return $this->modifiers;
    }

    public function getConstraints(): array
    {
        return $this->constraints;
    }

    public function addModifier(Modifier $modifier): void
    {
        $this->modifiers[] = $modifier;
    }

    public function addConstraint(Constraint $constraint): void
    {
        $this->constraints[] = $constraint;
    }

    public function getLinkedObject(): CategoryEntry
    {
        return SharedCategoryEntry::get($this->targetId);
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
            $element->getAttribute('targetId')->asString(),
            $element->getAttribute('primary')->asBoolean()
        );

        foreach($element->xpath('modifiers/modifier') as $modifier) {
            $result->addModifier(Modifier::fromXml($modifier));
        }

        foreach($element->xpath('constraints/constraint') as $constraint) {
            $result->addConstraint(Constraint::fromXml($constraint));
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
