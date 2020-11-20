<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;
use Closure;
use UnexpectedValueException;

class SelectionEntryGroup implements SelectionEntryGroupInterface
{
    private const NAME = 'selectionEntryGroup';

    private ?TreeInterface $parent;
    private string $id;
    private string $name;
    private bool $hidden;
    private bool $collective;
    private bool $import;
    private ?string $defaultSelectionEntryId;

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

    public function __construct(
        ?TreeInterface $parent,
        string $id,
        string $name,
        bool $hidden,
        bool $collective,
        bool $import,
        ?string $defaultSelectionEntryId
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

    public function getId(): string
    {
        return $this->id;
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
            if($matcher($selectionEntry->getId())) {
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
        return
            $this->constraints +
            $this->selectionEntries +
            $this->selectionEntryGroups +
            $this->entryLinks;
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

    public static function fromXml(?IdentifierInterface $parent, SimpleXmlElementFacade $element): ?self
    {
        if ($element === null) {
            return null;
        }

        if ($element->getName() !== self::NAME) {
            throw new UnexpectedNodeException('Received a ' . $element->getName() . ', expected ' . self::NAME);
        }

        $result = new static(
            $parent,
            $element->getAttribute('id')->asString(),
            $element->getAttribute('name')->asString(),
            $element->getAttribute('hidden')->asBoolean(),
            $element->getAttribute('collective')->asBoolean(),
            $element->getAttribute('import')->asBoolean(),
            $element->getAttribute('defaultSelectionEntryId')->asString(),
        );

        foreach($element->xpath('modifiers/modifier') as $modifier) {
            $result->addModifier(Modifier::fromXml($modifier));
        }

        foreach($element->xpath('constraints/constraint') as $constraint) {
            $result->addConstraint(Constraint::fromXml($constraint));
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
}
