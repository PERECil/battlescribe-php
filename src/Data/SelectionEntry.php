<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;
use Closure;
use UnexpectedValueException;

class SelectionEntry implements SelectionEntryInterface
{
    private const NAME = 'selectionEntry';

    private ?TreeInterface $parent;
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

    /** @var CategoryEntryInterface[] */
    private array $categoryEntries;

    /** @var RuleInterface[] */
    private array $rules;

    /** @var InfoGroupInterface[] */
    private array $infoGroups;

    public function __construct(
        ?IdentifierInterface $parent,
        string $id,
        string $name,
        bool $hidden,
        bool $collective,
        bool $import,
        SelectionEntryType $type
    )
    {
        $this->parent = $parent;

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

        $this->categoryEntries = [];
        $this->rules = [];
        $this->infoGroups = [];
    }

    public function getSharedId(): string
    {
        return $this->id;
    }

    public function getParent(): ?TreeInterface
    {
        return $this->parent;
    }

    public function getRoot(): TreeInterface
    {
        return $this->getParent()->getRoot();
    }

    public function findSelectionEntryByMatcher(Closure $matcher): array
    {
        $result = [];

        foreach($this->getSelectionEntries() as $selectionEntry) {
            if($matcher($selectionEntry)) {
                $result[] = $selectionEntry;
            }

            $result += $selectionEntry->findSelectionEntryByMatcher($matcher);
        }

        foreach($this->getSelectionEntryGroups() as $selectionEntryGroup) {
            $result += $selectionEntryGroup->findSelectionEntryByMatcher($matcher);
        }

        return $result;
    }

    public function getChildren(): array
    {
        // modifiers don't have an id,
        // remove it from children

        return
            $this->constraints +
            $this->profiles +
            $this->infoLinks +
            $this->categoryLinks +
            $this->selectionEntryGroups +
            $this->selectionEntries +
            $this->entryLinks +
            $this->costs +
            $this->rules;
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

    public function findCost(string $id): ?Cost
    {
        foreach($this->costs as $cost) {
            if($cost->getId() === $id) {
                return $cost;
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

    public function getCategoryEntries(): array
    {
        return $this->categoryEntries;
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
        } else if($linkedObject instanceof RuleInterface) {
            $this->addRule($linkedObject);
        } else if($linkedObject instanceof InfoGroupInterface) {
            $this->addInfoGroup($linkedObject);
        } else {
            throw new UnexpectedValueException();
        }
    }

    public function addInfoGroup(InfoGroupInterface $infoGroup): void
    {
        $this->infoGroups[] = $infoGroup;
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

    public function addRule(RuleInterface $rule): void
    {
        $this->rules[] = $rule;
    }

    public static function fromXml(?IdentifierInterface $parent, SimpleXmlElementFacade $element): ?self
    {
        if($element === null) {
            return null;
        }

        if($element->getName() !== self::NAME) {
            throw new UnexpectedNodeException( 'Received a '.$element->getName().', expected '.self::NAME);
        }

        $result = new static(
            $parent,
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
            $result->addSelectionEntryGroup(SelectionEntryGroup::fromXml($result, $selectionEntryGroup));
        }

        foreach($element->xpath('selectionEntries/selectionEntry') as $selectionEntry) {
            $result->addSelectionEntry(SelectionEntry::fromXml($result, $selectionEntry));
        }

        foreach($element->xpath('entryLinks/entryLink') as $entryLink) {
            $result->addEntryLink(EntryLink::fromXml($result, $entryLink));
        }

        foreach($element->xpath('costs/cost') as $cost) {
            $result->addCost(Cost::fromXml($cost));
        }

        return $result;
    }
}
