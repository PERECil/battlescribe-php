<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;

class ForceEntry
{
    private const NAME = 'forceEntry';

    private string $id;
    private string $name;
    private bool $hidden;

    /** @var CategoryLink[] */
    private array $categoryLinks;

    public function __construct(
        string $id,
        string $name,
        bool $hidden
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->hidden = $hidden;

        $this->categoryLinks = [];
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

    public function getCategoryLinks(): array
    {
        return $this->categoryLinks;
    }

    public function addCategoryLink(CategoryLink $categoryLink): void
    {
        $this->categoryLinks[] = $categoryLink;
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
        );

        foreach($element->xpath('categoryLinks/categoryLink') as $categoryLink) {
            $result->addCategoryLink(CategoryLink::fromXml($categoryLink));
        }

        return $result;
    }
}
