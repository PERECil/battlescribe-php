<?php

declare(strict_types=1);

namespace Battlescribe;

use UnexpectedValueException;

class SelectionEntryGroup implements SelectionEntryGroupInterface
{
    private const NAME = 'selectionEntryGroup';

    private string $id;
    private string $name;
    private bool $hidden;
    private bool $collective;
    private bool $import;
    private ?string $defaultSelectionEntryId;

    /** @var Constraint[] */
    private array $constraints;

    /** @var SelectionEntryInterface[] */
    private array $selectionEntries;

    /** @var SelectionEntryGroup[] */
    private array $selectionEntryGroups;

    /** @var EntryLink[] */
    private array $entryLinks;

    public function __construct(
        string $id,
        string $name,
        bool $hidden,
        bool $collective,
        bool $import,
        ?string $defaultSelectionEntryId
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->hidden = $hidden;
        $this->collective = $collective;
        $this->import = $import;
        $this->defaultSelectionEntryId = $defaultSelectionEntryId;

        $this->constraints = [];
        $this->selectionEntries = [];
        $this->selectionEntryGroups = [];
        $this->entryLinks = [];
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

    public function getDefaultSelectionEntryId(): ?string
    {
        return $this->defaultSelectionEntryId;
    }

    /**
     * @return Constraint[]
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    /**
     * @return SelectionEntry[]
     */
    public function getSelectionEntries(): array
    {
        return $this->selectionEntries;
    }

    /**
     * @return SelectionEntryGroup[]
     */
    public function getSelectionEntryGroups(): array
    {
        return $this->selectionEntryGroups;
    }

    /**
     * @return EntryLink[]
     */
    public function getEntryLinks(): array
    {
        return $this->entryLinks;
    }

    public function addConstraint(Constraint $constraint): void
    {
        $this->constraints[] = $constraint;
    }

    public function addSelectionEntry(SelectionEntryInterface $selectionEntry): void
    {
        $this->selectionEntries[] = $selectionEntry;
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

    public static function fromXml(SimpleXmlElementFacade $element): ?self
    {
        if ($element === null) {
            return null;
        }

        if ($element->getName() !== self::NAME) {
            throw new UnexpectedNodeException('Received a ' . $element->getName() . ', expected ' . self::NAME);
        }

        $result = new static(
            $element->getAttribute('id')->asString(),
            $element->getAttribute('name')->asString(),
            $element->getAttribute('hidden')->asBoolean(),
            $element->getAttribute('collective')->asBoolean(),
            $element->getAttribute('import')->asBoolean(),
            $element->getAttribute('defaultSelectionEntryId')->asString(),
        );

        foreach($element->xpath('constraints/constraint') as $constraint) {
            $result->addConstraint(Constraint::fromXml($constraint));
        }

        foreach($element->xpath('selectionEntries/selectionEntry') as $selectionEntry) {
            $result->addSelectionEntry(SelectionEntry::fromXml($selectionEntry));
        }

        foreach($element->xpath('sharedSelectionEntries/selectionEntry') as $selectionEntry) {
            $result->addSelectionEntry(SelectionEntry::fromXml($selectionEntry));
        }

        foreach($element->xpath('entryLinks/entryLink') as $entryLink) {
            $result->addEntryLink(EntryLink::fromXml($entryLink));
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
            'default_selection_entry_id' => $this->defaultSelectionEntryId,

            // Non needed because converted to references
            // 'entry_links' => $this->entryLinks,
        ];
    }
}
