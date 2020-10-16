<?php

declare(strict_types=1);

namespace Battlescribe;

class SelectionEntryReference implements SelectionEntryInterface
{
    private string $targetId;

    public function __construct(string $targetId)
    {
        $this->targetId = $targetId;
    }

    public function getId(): string
    {
        return $this->targetId;
    }

    public function getName(): string
    {
        return SharedSelectionEntry::get($this->targetId)->getName();
    }

    public function isHidden(): bool
    {
        return SharedSelectionEntry::get($this->targetId)->isHidden();
    }

    public function isCollective(): bool
    {
        return SharedSelectionEntry::get($this->targetId)->isCollective();
    }

    public function isImport(): bool
    {
        return SharedSelectionEntry::get($this->targetId)->isImport();
    }

    public function getType(): SelectionEntryType
    {
        return SharedSelectionEntry::get($this->targetId)->getType();
    }

    public function getModifiers(): array
    {
        return SharedSelectionEntry::get($this->targetId)->getModifiers();
    }

    public function getConstraints(): array
    {
        return SharedSelectionEntry::get($this->targetId)->getConstraints();
    }

    public function getProfiles(): array
    {
        return SharedSelectionEntry::get($this->targetId)->getProfiles();
    }

    public function getInfoLinks(): array
    {
        return SharedSelectionEntry::get($this->targetId)->getInfoLinks();
    }

    public function getCategoryLinks(): array
    {
        return SharedSelectionEntry::get($this->targetId)->getCategoryLinks();
    }

    public function getSelectionEntryGroups(): array
    {
        return SharedSelectionEntry::get($this->targetId)->getSelectionEntryGroups();
    }

    public function getSelectionEntries(): array
    {
        return SharedSelectionEntry::get($this->targetId)->getSelectionEntries();
    }

    public function getEntryLinks(): array
    {
        return SharedSelectionEntry::get($this->targetId)->getEntryLinks();
    }

    public function getCosts(): array
    {
        return SharedSelectionEntry::get($this->targetId)->getCosts();
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return SharedSelectionEntry::get($this->targetId)->jsonSerialize();
    }
}
