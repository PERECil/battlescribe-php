<?php

declare(strict_types=1);

namespace Battlescribe;

use JsonSerializable;
use SimpleXMLElement;

class Catalog implements JsonSerializable
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

    /** @var CategoryEntry[] */
    private array $categoryEntries;

    /** @var Rule[] */
    private array $rules;

    /** @var SelectionEntryInterface[] */
    private array $selectionEntries;

    /** @var SelectionEntryGroupInterface[] */
    private array $selectionEntryGroups;

    /** @var ProfileInterface[] */
    private array $profiles;

    /** @var CatalogueLink[] */
    private array $catalogueLinks;

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
        $this->rules = [];
        $this->selectionEntries = [];
        $this->selectionEntryGroups = [];
        $this->profiles = [];
        $this->catalogueLinks = [];
    }

    public function getId(): string
    {
        return $this->id;
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

    /**
     * @return ProfileType[]
     */
    public function getProfileTypes(): array
    {
        return $this->profileTypes;
    }

    /**
     * @return CategoryEntry[]
     */
    public function getCategoryEntries(): array
    {
        return $this->categoryEntries;
    }

    /**
     * @return Rule[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * @return SelectionEntryInterface[]
     */
    public function getSelectionEntries(): array
    {
        return $this->selectionEntries;
    }

    public function addProfileType(ProfileType $profileType): void
    {
        $this->profileTypes[] = $profileType;
    }

    public function addCategoryEntry(CategoryEntry $categoryEntry): void
    {
        $this->categoryEntries[] = $categoryEntry;
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
            $result->addCategoryEntry(CategoryEntry::fromXml($categoryEntry));
        }

        foreach($element->xpath('rules/rule') as $rule) {
            $result->addRule(Rule::fromXml($rule));
        }

        foreach($element->xpath('sharedSelectionEntries/selectionEntry') as $selectionEntry) {
            $result->addSelectionEntry(SharedSelectionEntry::fromXml($selectionEntry));
        }

        foreach($element->xpath('sharedSelectionEntryGroups/selectionEntryGroup') as $selectionEntryGroup) {
            $result->addSelectionEntryGroup(SharedSelectionEntryGroup::fromXml($selectionEntryGroup));
        }

        foreach($element->xpath('sharedProfiles/profile') as $profile) {
            $result->addProfile(SharedProfile::fromXml($profile));
        }

        foreach($element->xpath('catalogueLinks/catalogueLink') as $catalogueLink) {
            $result->addCatalogueLink(CatalogueLink::fromXml($catalogueLink));
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
            'selection_entries' => $this->selectionEntries,
        ];
    }
}
