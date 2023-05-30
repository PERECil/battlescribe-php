<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Closure;

class SelectionEntryGroupReference implements SelectionEntryGroupInterface
{
    private EntryLink $entryLink;
    private Identifier $targetId;

    public function __construct(EntryLink $entryLink, Identifier $targetId)
    {
        $this->entryLink = $entryLink;
        $this->targetId = $targetId;
    }

    public function getId(): Identifier
    {
        return $this->entryLink->getId();
    }

    public function getSharedId(): Identifier
    {
        return $this->targetId;
    }

    public function getParent(): ?TreeInterface
    {
        return $this->entryLink->getParent();
    }

    public function getRoot(): TreeInterface
    {
        return $this->getParent()->getRoot();
    }

    public function getGameSystem(): ?GameSystem
    {
        return $this->getParent()->getGameSystem();
    }

    public function getChildren(): array
    {
        return [];
    }

    public function getName(): string
    {
        return SharedSelectionEntryGroup::get($this->targetId)->getName();
    }

    public function isHidden(): bool
    {
        return SharedSelectionEntryGroup::get($this->targetId)->isHidden();
    }

    public function isCollective(): bool
    {
        return SharedSelectionEntryGroup::get($this->targetId)->isCollective();
    }

    public function isImport(): bool
    {
        return SharedSelectionEntryGroup::get($this->targetId)->isImport();
    }

    public function getDefaultSelectionEntryId(): ?Identifier
    {
        return SharedSelectionEntryGroup::get($this->targetId)->getDefaultSelectionEntryId();
    }

    public function getModifiers(): array
    {
        return SharedSelectionEntryGroup::get($this->targetId)->getModifiers();
    }

    public function getConstraints(): array
    {
        return SharedSelectionEntryGroup::get($this->targetId)->getConstraints();
    }

    public function getSelectionEntries(): array
    {
        return SharedSelectionEntryGroup::get($this->targetId)->getSelectionEntries();
    }

    public function getSelectionEntryGroups(): array
    {
        return SharedSelectionEntryGroup::get($this->targetId)->getSelectionEntryGroups();
    }

    public function getEntryLinks(): array
    {
        return SharedSelectionEntryGroup::get($this->targetId)->getEntryLinks();
    }

    public function findSelectionEntryByName(string $name): array
    {
        return SharedSelectionEntryGroup::get($this->targetId)->findSelectionEntryByName($name);
    }

    public function findByMatcher(Closure $matcher): array
    {
        return SharedSelectionEntryGroup::get($this->targetId)->findByMatcher($matcher);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'selection_entries' => $this->getSelectionEntries(),
            'default_selection_entry_id' => $this->getDefaultSelectionEntryId(),

            // Non needed because converted to references
            // 'entry_links' => $this->entryLinks,
        ];
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
