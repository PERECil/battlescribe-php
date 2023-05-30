<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;
use Closure;
use UnexpectedValueException;

class GameSystem implements IdentifierInterface, TreeInterface
{
    use RootTrait;

    private const NAME = 'gameSystem';

    private Identifier $id;
    private string $name;
    private int $revision;
    private string $battlescribeVersion;
    private ?string $authorUrl;

    /** @var Publication[] */
    private array $publications;

    /** @var CostType[] */
    private array $costTypes;

    /** @var ProfileType[] */
    private array $profileTypes;

    /** @var CategoryEntryInterface[] */
    private array $categoryEntries;

    /** @var ForceEntry[] */
    private array $forceEntries;

    /** @var EntryLink[] */
    private array $entryLinks;

    /** @var SelectionEntryInterface[] */
    private array $selectionEntries;

    /** @var SelectionEntryGroupInterface[] */
    private array $selectionEntryGroups;

    /** @var ProfileInterface[] */
    private array $profiles;

    public function __construct(
        Identifier $id,
        string $name,
        int $revision,
        string $battlescribeVersion,
        ?string $authorUrl
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->revision = $revision;
        $this->battlescribeVersion = $battlescribeVersion;
        $this->authorUrl = $authorUrl;

        $this->publications = [];
        $this->costTypes = [];
        $this->profileTypes = [];
        $this->categoryEntries = [];
        $this->forceEntries = [];
        $this->entryLinks = [];
        $this->selectionEntries = [];
        $this->selectionEntryGroups = [];
        $this->profiles = [];
    }

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getParent(): ?TreeInterface
    {
        return null;
    }

    public function getRoot(): TreeInterface
    {
        return $this;
    }

    /** @inheritDoc */
    public function getChildren(): array
    {
        return array_merge(
            $this->publications,
            $this->costTypes,
            $this->profileTypes,
            $this->categoryEntries,
            $this->forceEntries,
            $this->entryLinks,
            $this->selectionEntries,
            $this->selectionEntryGroups,
            $this->profiles
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRevision(): int
    {
        return $this->revision;
    }

    public function getBattlescribeVersion(): string
    {
        return $this->battlescribeVersion;
    }

    public function getAuthorUrl(): string
    {
        return $this->authorUrl;
    }

    public function getPublications(): array
    {
        return $this->publications;
    }

    public function getCostTypes(): array
    {
        return $this->costTypes;
    }

    public function getProfileTypes(): array
    {
        return $this->profileTypes;
    }

    public function getCategoryEntries(): array
    {
        return $this->categoryEntries;
    }

    /** @return ForceEntry[] */
    public function getForceEntries(): array
    {
        return $this->forceEntries;
    }

    public function getEntryLinks(): array
    {
        return $this->entryLinks;
    }

    public function getSelectionEntries(): array
    {
        return $this->selectionEntries;
    }

    public function getSelectionEntryGroups(): array
    {
        return $this->selectionEntryGroups;
    }

    public function getProfiles(): array
    {
        return $this->profiles;
    }

    public static function fromFile(string $file): ?self
    {
        $binaryFile = $file.'.bin';

        if(file_exists($binaryFile)) {
            return unserialize(file_get_contents($binaryFile));
        } else {
            $data  = file_get_contents($file);

            $data = str_replace( ' xmlns="http://www.battlescribe.net/schema/gameSystemSchema"', '', $data );

            $element = simplexml_load_string( $data);

            $xml = new SimpleXmlElementFacade($element);

            $gameSystem = self::fromXml($xml);

            file_put_contents($binaryFile, serialize($gameSystem));

            return $gameSystem;
        }
    }

    public function addPublication(Publication $publication): void
    {
        $this->publications[] = $publication;
    }

    public function addCostType(CostType $costType): void
    {
        $this->costTypes[] = $costType;
    }

    public function addProfileType(ProfileType $profileType): void
    {
        $this->profileTypes[] = $profileType;
    }

    public function addCategoryEntry(CategoryEntryInterface $categoryEntry): void
    {
        $this->categoryEntries[] = $categoryEntry;
    }

    public function addForceEntry(ForceEntry $forceEntry): void
    {
        $this->forceEntries[] = $forceEntry;
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

    public function addSelectionEntry(SelectionEntryInterface $selectionEntry): void
    {
        $this->selectionEntries[] = $selectionEntry;
    }

    public function addSelectionEntryGroup(SelectionEntryGroupInterface $selectionEntryGroup): void
    {
        $this->selectionEntryGroups[] = $selectionEntryGroup;
    }

    public function addProfile(ProfileInterface $profile): void
    {
        $this->profiles[] = $profile;
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
            $element->getAttribute('id')->asIdentifier(),
            $element->getAttribute('name')->asString(),
            $element->getAttribute('revision')->asInt(),
            $element->getAttribute('battleScribeVersion')->asString(),
            $element->getAttribute('authorUrl')->asString()
        );

        foreach($element->xpath('publications/publication') as $publication) {
            $result->addPublication(Publication::fromXml($result, $publication));
        }

        foreach($element->xpath('costTypes/costType') as $costType) {
            $result->addCostType(CostType::fromXml($result, $costType));
        }

        foreach($element->xpath('profileTypes/profileType') as $profileType) {
            $result->addProfileType(ProfileType::fromXml($result, $profileType));
        }

        foreach($element->xpath('categoryEntries/categoryEntry') as $categoryEntry) {
            $result->addCategoryEntry(SharedCategoryEntry::fromXml($result, $categoryEntry));
        }

        foreach($element->xpath('forceEntries/forceEntry') as $forceEntry) {
            $result->addForceEntry(ForceEntry::fromXml($result, $forceEntry));
        }

        foreach($element->xpath('entryLinks/entryLink') as $entryLink) {
            $result->addEntryLink(EntryLink::fromXml($result, $entryLink));
        }

        foreach($element->xpath('sharedSelectionEntries/selectionEntry') as $selectionEntry) {
            $result->addSelectionEntry(SharedSelectionEntry::fromXml($result, $selectionEntry));
        }

        foreach($element->xpath('sharedSelectionEntryGroups/selectionEntryGroup') as $selectionEntry) {
            $result->addSelectionEntryGroup(SharedSelectionEntryGroup::fromXml($result, $selectionEntry));
        }

        foreach($element->xpath('sharedProfiles/profile') as $profile) {
            $result->addProfile(SharedProfile::fromXml($result, $profile));
        }

        return $result;
    }
}
