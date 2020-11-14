<?php

declare(strict_types=1);

namespace Battlescribe;

use JsonSerializable;
use SimpleXMLElement;
use UnexpectedValueException;

class Catalog implements IdentifierInterface, TreeInterface
{
    private const NAME = 'catalogue';

    private string $id;
    private string $name;
    private int $revision;
    private string $battleScribeVersion;
    private bool $isLibrary;
    private string $gameSystemId;
    private int $gameSystemRevision;

    /** @var ProfileType[] */
    private array $profileTypes;

    /** @var CategoryEntryInterface[] */
    private array $categoryEntries;

    /** @var EntryLink[] */
    private array $entryLinks;

    /** @var Rule[] */
    private array $rules;

    /** @var SelectionEntryInterface[] */
    private array $sharedSelectionEntries;

    /** @var SelectionEntryGroupInterface[] */
    private array $sharedSelectionEntryGroups;

    /** @var ProfileInterface[] */
    private array $sharedProfiles;

    /** @var CatalogueLink[] */
    private array $catalogueLinks;

    /** @var SelectionEntryInterface[] */
    private array $selectionEntries;

    /** @var SelectionEntryGroupInterface[] */
    private array $selectionEntryGroups;

    /** @var ProfileInterface[] */
    private array $profiles;

    public function __construct(
        string $id,
        string $name,
        int $revision,
        string $battleScribeVersion,
        bool $isLibrary,
        string $gameSystemId,
        int $gameSystemRevision
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->revision = $revision;
        $this->battleScribeVersion = $battleScribeVersion;
        $this->isLibrary = $isLibrary;
        $this->gameSystemId = $gameSystemId;
        $this->gameSystemRevision = $gameSystemRevision;

        $this->profileTypes = [];
        $this->categoryEntries = [];
        $this->entryLinks = [];
        $this->rules = [];
        $this->sharedSelectionEntries =[];
        $this->sharedSelectionEntryGroups = [];
        $this->sharedProfiles = [];
        $this->catalogueLinks = [];

        // This contains references to the shared selections/groups/entries
        // that are imported through entry links. Entry links may override
        // some of the values with modifier, that's why we can't blindly use
        // the shared items.
        $this->selectionEntries = [];
        $this->selectionEntryGroups = [];
        $this->profiles = [];
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getParent(): ?TreeInterface
    {
        return null;
    }

    public function getChildren(): array
    {
        return
            $this->profileTypes+
            $this->categoryEntries+
            $this->entryLinks +
            $this->rules +
            $this->sharedSelectionEntries +
            $this->sharedSelectionEntryGroups +
            $this->sharedProfiles +
            $this->catalogueLinks;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRevision(): int
    {
        return $this->revision;
    }

    public function getBattleScribeVersion(): string
    {
        return $this->battleScribeVersion;
    }

    public function isLibrary(): bool
    {
        return $this->isLibrary;
    }

    public function getGameSystemId(): string
    {
        return $this->gameSystemId;
    }

    public function getGameSystemRevision(): int
    {
        return $this->gameSystemRevision;
    }

    /** @return ProfileType[] */
    public function getProfileTypes(): array
    {
        return $this->profileTypes;
    }

    /** @return CategoryEntryInterface[] */
    public function getCategoryEntries(): array
    {
        return $this->categoryEntries;
    }

    /** @return Rule[] */
    public function getRules(): array
    {
        return $this->rules;
    }

    /** @return SelectionEntryInterface[] */
    public function getSelectionEntries(): array
    {
        return $this->selectionEntries;
    }

    public function addProfileType(ProfileType $profileType): void
    {
        $this->profileTypes[] = $profileType;
    }

    public function addCategoryEntry(CategoryEntryInterface $categoryEntry): void
    {
        $this->categoryEntries[] = $categoryEntry;
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

    public function addRule(Rule $rule): void
    {
        $this->rules[] = $rule;
    }

    public function addSelectionEntry(SelectionEntryInterface $selectionEntry): void
    {
        $this->selectionEntries[] = $selectionEntry;
    }

    public function findSelectionEntry(string $id): ?SelectionEntryInterface
    {
        foreach($this->selectionEntries as $selectionEntry) {
            if($selectionEntry->getId() === $id) {
                return $selectionEntry;
            }
        }

        return null;
    }

    public function addSelectionEntryGroup(SelectionEntryGroupInterface $selectionEntryGroup): void
    {
        $this->selectionEntryGroups[] = $selectionEntryGroup;
    }

    public function addProfile(ProfileInterface $profile): void
    {
        $this->profiles[] = $profile;
    }

    public function addSharedSelectionEntry(SelectionEntryInterface $selectionEntry): void
    {
        $this->sharedSelectionEntries[] = $selectionEntry;
    }

    public function addSharedSelectionEntryGroup(SelectionEntryGroupInterface $selectionEntryGroup): void
    {
        $this->sharedSelectionEntryGroups[] = $selectionEntryGroup;
    }

    public function addSharedProfile(ProfileInterface $profile): void
    {
        $this->sharedProfiles[] = $profile;
    }

    public function addCatalogueLink(CatalogueLink $catalogueLink): void
    {
        $this->catalogueLinks[] = $catalogueLink;
    }

    public static function fromFile(string $file): ?self
    {
        $data  = file_get_contents($file);

        $data = str_replace( ' xmlns="http://www.battlescribe.net/schema/catalogueSchema"', '', $data );

        $element = simplexml_load_string( $data);

        $xml = new SimpleXmlElementFacade($element);

        return self::fromXml($xml);
    }

    public static function fromXml(?SimpleXMLElementFacade $element): ?self
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
            $element->getAttribute('revision')->asInt(),
            $element->getAttribute('battleScribeVersion')->asString(),
            $element->getAttribute('library')->asBoolean(),
            $element->getAttribute('gameSystemId')->asString(),
            $element->getAttribute('gameSystemRevision')->asInt()
        );

        foreach($element->xpath('profileTypes/profileType') as $profileType) {
            $result->addProfileType(ProfileType::fromXml($profileType));
        }

        foreach($element->xpath('categoryEntries/categoryEntry') as $categoryEntry) {
            $result->addCategoryEntry(SharedCategoryEntry::fromXml($categoryEntry));
        }

        foreach($element->xpath('entryLinks/entryLink') as $entryLink) {
            $result->addEntryLink(EntryLink::fromXml($result, $entryLink));
        }

        foreach($element->xpath('rules/rule') as $rule) {
            $result->addRule(Rule::fromXml($rule));
        }

        foreach($element->xpath('sharedSelectionEntries/selectionEntry') as $selectionEntry) {
            $result->addSharedSelectionEntry(SharedSelectionEntry::fromXml($result, $selectionEntry));
        }

        foreach($element->xpath('sharedSelectionEntryGroups/selectionEntryGroup') as $selectionEntryGroup) {
            $result->addSharedSelectionEntryGroup(SharedSelectionEntryGroup::fromXml($result, $selectionEntryGroup));
        }

        foreach($element->xpath('sharedProfiles/profile') as $profile) {
            $result->addSharedProfile(SharedProfile::fromXml($profile));
        }

        foreach($element->xpath('catalogueLinks/catalogueLink') as $catalogueLink) {
            $result->addCatalogueLink(CatalogueLink::fromXml($catalogueLink));
        }

        return $result;
    }
}
