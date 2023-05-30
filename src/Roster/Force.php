<?php

declare(strict_types=1);


namespace Battlescribe\Roster;

use Battlescribe\Data\BranchTrait;
use Battlescribe\Data\Identifier;
use Battlescribe\Data\IdentifierInterface;
use Battlescribe\Data\Publication;
use Battlescribe\Data\TreeInterface;
use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;
use Exception;

/** One to one XML mapping */
class Force implements TreeInterface, IdentifierInterface, ForceInterface
{
    use BranchTrait;

    private const NAME = 'force';

    private Identifier $id;
    private string $name;
    private string $entryId;
    private Identifier $catalogueId;
    private int $catalogueRevision;
    private string $catalogueName;

    /** @var Selection[] */
    private array $selections;

    /** @var Publication[]  */
    private array $publications;

    /** @var Category[] */
    private array $categories;

    public function __construct(
        ?TreeInterface $parent,
        Identifier $id,
        string $name,
        string $entryId,
        Identifier $catalogueId,
        int $catalogueRevision,
        string $catalogueName
    )
    {
        $this->parent = $parent;

        $this->id = $id;
        $this->name = $name;
        $this->entryId = $entryId;
        $this->catalogueId = $catalogueId;
        $this->catalogueRevision = $catalogueRevision;
        $this->catalogueName = $catalogueName;

        $this->selections = [];
        $this->publications = [];
        $this->categories = [];
    }

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /** @inheritDoc */
    public function getChildren(): array
    {
        return array_merge(
            $this->selections,
            $this->publications,
            $this->categories
        );
    }

    public function getEntryId(): string
    {
        return $this->entryId;
    }

    public function getCatalogueId(): Identifier
    {
        return $this->catalogueId;
    }

    public function getCatalogueRevision(): int
    {
        return $this->catalogueRevision;
    }

    public function getCatalogueName(): string
    {
        return $this->catalogueName;
    }

    public function addSelection(SelectionInterface $selection): void
    {
        $this->selections[] = $selection;
    }

    /** @return Selection[] */
    public function getSelections(): array
    {
        return $this->selections;
    }

    public function addPublication(Publication $publication): void
    {
        $this->publications[] = $publication;
    }

    /** @return Publication[] */
    public function getPublications(): array
    {
        return $this->publications;
    }

    public function addCategory(Category $category): void
    {
        $this->categories[] = $category;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function findPublicationById(string $id): ?Publication
    {
        foreach($this->getPublications() as $publication) {
            if($publication->getId()->equals($id)) {
                return $publication;
            }
        }

        return null;
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
            $element->getAttribute('entryId')->asString(),
            $element->getAttribute('catalogueId')->asIdentifier(),
            $element->getAttribute('catalogueRevision')->asInt(),
            $element->getAttribute('catalogueName')->asString()
        );

        foreach($element->xpath('selections/selection') as $selection) {
            $result->addSelection(Selection::fromXml($result, $selection));
        }

        foreach($element->xpath('publications/publication') as $publication) {
            $result->addPublication(Publication::fromXml($result, $publication));
        }

        foreach($element->xpath('categories/category') as $category) {
            $result->addCategory(Category::fromXml($result, $category));
        }

        return $result;
    }

    public function getCategoryLinks(): array
    {
        throw new Exception('Not implemented, does this need to be implemented?');
    }
}

