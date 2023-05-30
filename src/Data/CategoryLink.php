<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;

class CategoryLink implements IdentifierInterface, TreeInterface
{
    use BranchTrait;

    private const NAME = 'categoryLink';

    private Identifier $id;
    private ?string $name;
    private bool $hidden;
    private Identifier $targetId;
    private bool $primary;

    /** @var Modifier[] */
    private array $modifiers;

    /** @var Constraint[] */
    private array $constraints;

    public function __construct(
        ?TreeInterface $parent,
        Identifier $id,
        ?string $name,
        bool $hidden,
        Identifier $targetId,
        bool $primary
    )
    {
        $this->parent = $parent;

        $this->id = $id;
        $this->name = $name;
        $this->hidden = $hidden;
        $this->targetId = $targetId;
        $this->primary = $primary;

        $this->modifiers = [];
        $this->constraints = [];
    }

    /** @inheritDoc */
    public function getChildren(): array
    {
        return array_merge(
            $this->modifiers,
            $this->constraints
        );
    }

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function getTargetId(): Identifier
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

    public function getLinkedObject(): CategoryEntryInterface
    {
        return new CategoryEntryReference($this, $this->targetId);
    }

    public static function fromXml(?TreeInterface $parent, ?SimpleXmlElementFacade $element): ?self
    {
        if($element === null) {
            return null;
        }

        if($element->getName() !== self::NAME) {
            throw new UnexpectedNodeException( 'Received a '.$element->getName().', expected '.self::NAME);
        }

        $result = new self(
            $parent,
            $element->getAttribute('id')->asIdentifier(),
            $element->getAttribute('name')->asString(),
            $element->getAttribute('hidden')->asBoolean(),
            $element->getAttribute('targetId')->asIdentifier(),
            $element->getAttribute('primary')->asBoolean()
        );

        foreach($element->xpath('modifiers/modifier') as $modifier) {
            $result->addModifier(Modifier::fromXml($result, $modifier));
        }

        foreach($element->xpath('constraints/constraint') as $constraint) {
            $result->addConstraint(Constraint::fromXml($result, $constraint));
        }

        return $result;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
