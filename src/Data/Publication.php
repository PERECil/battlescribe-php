<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;

class Publication implements TreeInterface, IdentifierInterface
{
    use LeafTrait;

    private const NAME = 'publication';

    private Identifier $id;
    private string $name;
    private ?string $shortName;
    private ?string $publisher;
    private ?string $publicationDate;

    public function __construct(
        ?TreeInterface $parent,
        Identifier $id,
        string $name,
        ?string $shortName,
        ?string $publisher,
        ?string $publicationDate
    )
    {
        $this->parent = $parent;

        $this->id = $id;
        $this->name = $name;
        $this->shortName = $shortName;
        $this->publisher = $publisher;
        $this->publicationDate = $publicationDate;
    }

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function getPublisher(): ?string
    {
        return $this->publisher;
    }

    public function getPublicationDate(): ?string
    {
        return $this->publicationDate;
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
            $element->getAttribute('shortName')->asString(),
            $element->getAttribute('publisher')->asString(),
            $element->getAttribute('publicationDate')->asString(),
        );
    }
}
