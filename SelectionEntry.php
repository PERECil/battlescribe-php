<?php

declare(strict_types=1);

namespace Battlescribe;

use UnexpectedValueException;

class SelectionEntry implements SelectionEntryInterface
{
    private const NAME = 'selectionEntry';

    private string $id;
    private string $name;
    private bool $hidden;
    private bool $collective;
    private bool $import;
    private SelectionEntryType $type;

    /** @var Modifier[] */
    private array $modifiers;

    /** @var Constraint[] */
    private array $constraints;

    /** @var ProfileInterface[] */
    private array $profiles;

    /** @var InfoLink[] */
    private array $infoLinks;

    /** @var CategoryLink[] */
    private array $categoryLinks;

    /** @var SelectionEntryGroupInterface[] */
    private array $selectionEntryGroups;

    /** @var SelectionEntryInterface[] */
    private array $selectionEntries;

    /** @var EntryLink[] */
    private array $entryLinks;

    /** @var Cost[] */
    private array $costs;

    public function __construct(
        string $id,
        string $name,
        bool $hidden,
        bool $collective,
        bool $import,
        SelectionEntryType $type
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->hidden = $hidden;
        $this->collective = $collective;
        $this->import = $import;
        $this->type = $type;

        $this->modifiers = [];
        $this->constraints = [];
        $this->profiles = [];
        $this->infoLinks = [];
        $this->categoryLinks = [];
        $this->selectionEntryGroups = [];
        $this->selectionEntries = [];
        $this->entryLinks = [];
        $this->costs = [];
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

    public function isCollective(): bool
    {
        return $this->collective;
    }

    public function isImport(): bool
    {
        return $this->import;
    }

    public function getType(): SelectionEntryType
    {
        return $this->type;
    }

    public function getModifiers(): array
    {
        return $this->modifiers;
    }

    public function getConstraints(): array
    {
        return $this->constraints;
    }

    public function findConstraint(string $id): ?Constraint
    {
        foreach($this->constraints as $constraint) {
            if($constraint->getId() === $id) {
                return $constraint;
            }
        }

        return null;
    }

    public function getProfiles(): array
    {
        return $this->profiles;
    }

    public function getInfoLinks(): array
    {
        return $this->infoLinks;
    }

    public function getCategoryLinks(): array
    {
        return $this->categoryLinks;
    }

    public function getSelectionEntryGroups(): array
    {
        return $this->selectionEntryGroups;
    }

    public function getSelectionEntries(): array
    {
        return $this->selectionEntries;
    }

    public function getEntryLinks(): array
    {
        return $this->entryLinks;
    }

    public function getCosts(): array
    {
        return $this->costs;
    }

    public function addModifier(Modifier $modifier): void
    {
        $this->modifiers[] = $modifier;
    }

    public function addConstraint(Constraint $constraint): void
    {
        $this->constraints[] = $constraint;
    }

    public function addProfile(ProfileInterface $profile): void
    {
        $this->profiles[] = $profile;
    }

    public function addInfoLink(InfoLink $infoLink): void
    {
        $this->infoLinks[] = $infoLink;

        $linkedObject = $infoLink->getLinkedObject();

        if($linkedObject instanceof ProfileInterface) {
            $this->addProfile($linkedObject);
        } else {
            throw new UnexpectedValueException();
        }
    }

    public function addCategoryLink(CategoryLink $categoryLink): void
    {
        $this->categoryLinks[] = $categoryLink;
    }

    public function addSelectionEntryGroup(SelectionEntryGroupInterface $selectionEntryGroup): void
    {
        $this->selectionEntryGroups[] = $selectionEntryGroup;
    }

    public function addSelectionEntry(SelectionEntryInterface $selectionEntry): void
    {
        $this->selectionEntries[] = $selectionEntry;
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

    public function addCost(Cost $cost): void
    {
        $this->costs[] = $cost;
    }

    public static function fromXml(SimpleXmlElementFacade $element): ?self
    {
        if($element === null) {
            return null;
        }

        if($element->getName() !== self::NAME) {
            throw new UnexpectedNodeException( 'Received a '.$element->getName().', expected '.self::NAME);
        }

        $result = new static(
            $element->getAttribute('id')->asString(),
            $element->getAttribute('name')->asString(),
            $element->getAttribute('hidden')->asBoolean(),
            $element->getAttribute('collective')->asBoolean(),
            $element->getAttribute('import')->asBoolean(),
            $element->getAttribute('type')->asEnum(SelectionEntryType::class),
        );

        foreach($element->xpath('modifiers/modifier') as $modifier) {
            $result->addModifier(Modifier::fromXml($modifier));
        }

        foreach($element->xpath('constraints/constraint') as $constraint) {
            $result->addConstraint(Constraint::fromXml($constraint));
        }

        foreach($element->xpath('profiles/profile') as $profile) {
            $result->addProfile(Profile::fromXml($profile));
        }

        foreach($element->xpath('infoLinks/infoLink') as $infoLink) {
            $result->addInfoLink(InfoLink::fromXml($infoLink));
        }

        foreach($element->xpath('categoryLinks/categoryLink') as $categoryLink) {
            $result->addCategoryLink(CategoryLink::fromXml($categoryLink));
        }

        foreach($element->xpath('selectionEntryGroups/selectionEntryGroup') as $selectionEntryGroup) {
            $result->addSelectionEntryGroup(SelectionEntryGroup::fromXml($selectionEntryGroup));
        }

        foreach($element->xpath('selectionEntries/selectionEntry') as $selectionEntry) {
            $result->addSelectionEntry(SelectionEntry::fromXml($selectionEntry));
        }

        foreach($element->xpath('entryLinks/entryLink') as $entryLink) {
            $result->addEntryLink(EntryLink::fromXml($entryLink));
        }

        foreach($element->xpath('costs/cost') as $cost) {
            $result->addCost(Cost::fromXml($cost));
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
            'type' => $this->type,

            // TODO Check which are needed on front side
            'profiles' => $this->profiles,
            'category_links' => $this->categoryLinks,
            'selection_entry_groups' => $this->selectionEntryGroups,
            'selection_entries' => $this->selectionEntries,
            'costs' => $this->costs,

            // TODO remove this from front, it must be calculated on backend
            'modifiers' => $this->modifiers,
            'constraints' => $this->constraints,

            // Non needed because converted to references
            // 'entry_links' => $this->entryLinks,
            // 'info_links' => $this->infoLinks,
        ];
    }
}
