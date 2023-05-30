<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Roster\ForceInterface;
use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;

class ForceEntry implements TreeInterface, IdentifierInterface, NameInterface
{
    use BranchTrait;

    private const NAME = 'forceEntry';

    private Identifier $id;
    private string $name;
    private bool $hidden;

    /** @var CategoryLink[] */
    private array $categoryLinks;
    private array $constraints;
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

        $this->categoryLinks = [];
        $this->constraints = [];
        $this->modifiers = [];
    }

    public function addConstraint(ConstraintInterface $constraint): void
    {
        $this->constraints[] = $constraint;
    }

    public function addModifier(Modifier $modifier): void
    {
        $this->modifiers[] = $modifier;
    }

    /** @inheritDoc */
    public function getChildren(): array
    {
        return array_merge(
            $this->categoryLinks
        );
    }

    public function getId(): Identifier
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

    public function getCategoryLinks(): array
    {
        return $this->categoryLinks;
    }

    public function addCategoryLink(CategoryLink $categoryLink): void
    {
        $this->categoryLinks[] = $categoryLink;
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
        );

        foreach($element->xpath('categoryLinks/categoryLink') as $categoryLink) {
            $result->addCategoryLink(CategoryLink::fromXml($result, $categoryLink));
        }

        foreach($element->xpath('modifiers/modifier') as $modifier) {
            $result->addModifier(Modifier::fromXml($result, $modifier));
        }

        foreach($element->xpath('constraints/constraint') as $constraint) {
            $result->addConstraint(Constraint::fromXml($result, $constraint));
        }


        return $result;
    }
}
