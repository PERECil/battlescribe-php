<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;
use UnexpectedValueException;

class EntryLink implements IdentifierInterface, TreeInterface
{
    use BranchTrait;

    private const NAME = 'entryLink';

    private Identifier $id;
    private ?string $name;
    private bool $hidden;
    private bool $collective;
    private bool $import;
    private Identifier $targetId;
    private EntryLinkType $type;

    /** @var Modifier[] */
    private array $modifiers;

    /** @var CategoryLink[] */
    private array $categoryLinks;

    /** @var CategoryEntryInterface[] */
    private array $categoryEntries;

    public function __construct(
        ?TreeInterface $parent,
        Identifier $id,
        ?string $name,
        bool $hidden,
        bool $collective,
        bool $import,
        Identifier $targetId,
        EntryLinkType $type
    )
    {
        $this->parent = $parent;
        $this->id = $id;
        $this->name = $name;
        $this->hidden = $hidden;
        $this->collective = $collective;
        $this->import = $import;
        $this->targetId = $targetId;
        $this->type = $type;

        $this->modifiers = [];
        $this->categoryLinks = [];
        $this->categoryEntries = [];
    }

    /** @inheritDoc */
    public function getChildren(): array
    {
        return array_merge(
            $this->modifiers,
            $this->categoryLinks,
            $this->categoryEntries
        );
    }

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getParent(): ?TreeInterface
    {
        return $this->parent;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function isCollective(): bool
    {
        return $this->collective;
    }

    public function isImport(): bool
    {
        return $this->import;
    }

    public function getTargetId(): Identifier
    {
        return $this->targetId;
    }

    public function getType(): EntryLinkType
    {
        return $this->type;
    }

    /** @return Modifier[] */
    public function getModifiers(): array
    {
        return $this->modifiers;
    }

    public function addModifier(Modifier $modifier): void
    {
        $this->modifiers[] = $modifier;
    }

    /** @return CategoryLink[] */
    public function getCategoryLinks(): array
    {
        return $this->categoryLinks;
    }

    public function addCategoryLink(CategoryLink $categoryLink): void
    {
        $this->categoryLinks[] = $categoryLink;

        $linkedObject = $categoryLink->getLinkedObject();

        if($linkedObject instanceof CategoryEntryInterface) {
            $this->addCategoryEntry($linkedObject);
        } else {
            throw new UnexpectedValueException();
        }
    }

    public function addCategoryEntry(CategoryEntryInterface $categoryEntry): void
    {
        $this->categoryEntries[] = $categoryEntry;
    }

    public function getCategoryEntries() : array
    {
        return $this->categoryEntries;
    }

    /**
     * @return SelectionEntryGroupReference|SelectionEntryReference
     */
    public function getLinkedObject()
    {
        switch($this->type->getValue())
        {
            case EntryLinkType::SELECTION_ENTRY: return new SelectionEntryReference($this, $this->targetId);
            case EntryLinkType::SELECTION_ENTRY_GROUP: return new SelectionEntryGroupReference($this, $this->targetId);
            default:
                throw new UnexpectedValueException();
        }
    }

    public static function fromXml(?TreeInterface $parent, ?SimpleXMLElementFacade $element): ?self
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
            $element->getAttribute('collective')->asBoolean(),
            $element->getAttribute('import')->asBoolean(),
            $element->getAttribute('targetId')->asIdentifier(),
            $element->getAttribute('type')->asEnum(EntryLinkType::class),
        );

        foreach($element->xpath('modifiers/modifier') as $modifier) {
            $result->addModifier(Modifier::fromXml($result, $modifier));
        }

        foreach($element->xpath('categoryLinks/categoryLink') as $categoryLink) {
            $result->addCategoryLink(CategoryLink::fromXml($result, $categoryLink));
        }

        return $result;
    }

}
