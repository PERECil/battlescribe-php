<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;
use Closure;
use UnexpectedValueException;

class SelectionEntryGroup implements SelectionEntryGroupInterface
{
    use BranchTrait;

    private const NAME = 'selectionEntryGroup';

    private Identifier $id;
    private string $name;
    private bool $hidden;
    private bool $collective;
    private bool $import;
    private ?Identifier $defaultSelectionEntryId;

    /** @var Modifier[] */
    private array $modifiers;

    /** @var Constraint[] */
    private array $constraints;

    /** @var SelectionEntryInterface[] */
    private array $selectionEntries;

    /** @var SelectionEntryGroup[] */
    private array $selectionEntryGroups;

    /** @var EntryLink[] */
    private array $entryLinks;

    /** @var CategoryEntryInterface[] */
    private array $categoryEntries;

    public function __construct(
        ?TreeInterface $parent,
        Identifier $id,
        string $name,
        bool $hidden,
        bool $collective,
        bool $import,
        ?Identifier $defaultSelectionEntryId
    )
    {
        $this->parent = $parent;

        $this->id = $id;
        $this->name = $name;
        $this->hidden = $hidden;
        $this->collective = $collective;
        $this->import = $import;
        $this->defaultSelectionEntryId = $defaultSelectionEntryId;

        $this->modifiers = [];
        $this->constraints = [];
        $this->selectionEntries = [];
        $this->selectionEntryGroups = [];
        $this->entryLinks = [];
    }

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getSharedId(): Identifier
    {
        return $this->id;
    }

    public function getChildren(): array
    {
        return array_merge(
            $this->constraints,
            $this->selectionEntries,
            $this->selectionEntryGroups,
            $this->entryLinks
        );
    }

    public function getName(): string
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

    public function getDefaultSelectionEntryId(): ?Identifier
    {
        return $this->defaultSelectionEntryId;
    }

    public function getModifiers(): array
    {
        return $this->modifiers;
    }

    public function getConstraints(): array
    {
        return $this->constraints;
    }

    public function getSelectionEntries(): array
    {
        return $this->selectionEntries;
    }

    public function getSelectionEntryGroups(): array
    {
        return $this->selectionEntryGroups;
    }

    public function getEntryLinks(): array
    {
        return $this->entryLinks;
    }

    public function addModifier(Modifier $modifier): void
    {
        $this->modifiers[] = $modifier;
    }

    public function addConstraint(Constraint $constraint): void
    {
        $this->constraints[] = $constraint;
    }

    public function addSelectionEntry(SelectionEntryInterface $selectionEntry): void
    {
        $this->selectionEntries[] = $selectionEntry;
    }

    /** @return SelectionEntryInterface[] */
    public function findSelectionEntryByName(string $name): array
    {
        $result = [];

        foreach($this->selectionEntries as $selectionEntry) {
            if($selectionEntry->getName() === $name) {
                $result[] = $selectionEntry;
            }
        }

        return $result;
    }

    public function addSelectionEntryGroup(SelectionEntryGroupInterface $selectionEntryGroup): void
    {
        $this->selectionEntryGroups[] = $selectionEntryGroup;
    }

    public function addEntryLink(EntryLink $entryLink): void
    {
        $this->entryLinks[] = $entryLink;

        $linkedObject = $entryLink->getLinkedObject();

        if($linkedObject instanceof SelectionEntryInterface) {
            $this->addSelectionEntry($linkedObject);
        } elseif($linkedObject instanceof SelectionEntryGroupInterface) {
            $this->addSelectionEntryGroup($linkedObject);
        } else {
            throw new UnexpectedValueException();
        }
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

    public static function fromXml(?IdentifierInterface $parent, ?SimpleXmlElementFacade $element): ?self
    {
        if ($element === null) {
            return null;
        }

        if ($element->getName() !== self::NAME) {
            throw new UnexpectedNodeException('Received a ' . $element->getName() . ', expected ' . self::NAME);
        }

        $result = new static(
            $parent,
            $element->getAttribute('id')->asIdentifier(),
            $element->getAttribute('name')->asString(),
            $element->getAttribute('hidden')->asBoolean(),
            $element->getAttribute('collective')->asBoolean(),
            $element->getAttribute('import')->asBoolean(),
            $element->getAttribute('defaultSelectionEntryId')->asIdentifier(),
        );

        foreach($element->xpath('modifiers/modifier') as $modifier) {
            $result->addModifier(Modifier::fromXml($result, $modifier));
        }

        foreach($element->xpath('constraints/constraint') as $constraint) {
            $result->addConstraint(Constraint::fromXml($result, $constraint));
        }

        foreach($element->xpath('selectionEntries/selectionEntry') as $selectionEntry) {
            $result->addSelectionEntry(SelectionEntry::fromXml($result, $selectionEntry));
        }

        foreach($element->xpath('sharedSelectionEntries/selectionEntry') as $selectionEntry) {
            $result->addSelectionEntry(SelectionEntry::fromXml($result, $selectionEntry));
        }

        foreach($element->xpath('entryLinks/entryLink') as $entryLink) {
            $result->addEntryLink(EntryLink::fromXml($result, $entryLink));
        }

        return $result;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
