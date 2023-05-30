<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Query\Matcher;
use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;
use Closure;

class Rule implements RuleInterface
{
    use LeafTrait;

    private const NAME = 'rule';

    private Identifier $id;
    private string $name;
    private ?Identifier $publicationId;
    private bool $hidden;
    private ?string $description;

    /** @var Modifier[] */
    private array $modifiers;

    public function __construct(
        ?TreeInterface $parent,
        Identifier $id,
        string $name,
        ?Identifier $publicationId,
        bool $hidden,
        ?string $description
    )
    {
        $this->parent = $parent;

        $this->id = $id;
        $this->name = $name;
        $this->publicationId = $publicationId;
        $this->hidden = $hidden;
        $this->description = $description;

        $this->modifiers = [];
    }

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPublicationId(): ?Identifier
    {
        return $this->publicationId;
    }

    public function getPublication(): ?Publication
    {
        return $this->getGameSystem()->findByMatcher(Matcher::id($this->publicationId))[0] ?? null;
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
            $element->getAttribute('publicationId')->asIdentifier(),
            $element->getAttribute('hidden')->asBoolean(),
            $element->xpath('description')->current()->asString()
        );

        foreach($element->xpath('modifiers/modifier') as $modifier) {
            $result->addModifier(Modifier::fromXml($result, $modifier));
        }

        return $result;
    }

    /** @inheritDoc */
    public function getChildren(): array
    {
        return array_merge(
            $this->modifiers,
        );
    }
}
