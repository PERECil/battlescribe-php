<?php

declare(strict_types=1);

namespace Battlescribe;

class SelectionEntryGroupReference implements SelectionEntryGroupInterface
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

    public function getDefaultSelectionEntryId(): ?string
    {
        return SharedSelectionEntryGroup::get($this->targetId)->getDefaultSelectionEntryId();
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

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return SharedSelectionEntryGroup::get($this->targetId)->jsonSerialize();
    }
}
