<?php

declare(strict_types=1);

namespace Battlescribe;

class SelectionEntryReference implements SelectionEntryInterface
{
    private EntryLink $entryLink;
    private string $targetId;

    public function __construct(EntryLink $entryLink, string $targetId)
    {
        $this->entryLink = $entryLink;
        $this->targetId = $targetId;
    }

    public function getParent(): ?TreeInterface
    {
        return $this->entryLink->getParent();
    }

    public function getChildren(): array
    {
        return [];
    }

    public function getId(): string
    {
        return $this->entryLink->getId();
    }

    public function getSharedId(): string
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
        // The entry link can override and add modifiers to the selection entry
        return array_merge( SharedSelectionEntry::get($this->targetId)->getModifiers(), $this->entryLink->getModifiers() );
    }

    public function getConstraints(): array
    {
        return SharedSelectionEntry::get($this->targetId)->getConstraints();
    }

    public function findConstraint(string $id): ?Constraint
    {
        return SharedSelectionEntry::get($this->targetId)->findConstraint($id);
    }

    public function findCost(string $id): ?Cost
    {
        return SharedSelectionEntry::get($this->targetId)->findCost($id);
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
        // The entry link can override and add category links to the selection entry
        return array_merge( SharedSelectionEntry::get($this->targetId)->getCategoryLinks(), $this->entryLink->getCategoryLinks() );
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

    public function getCategoryEntries(): array
    {
        return array_merge( SharedSelectionEntry::get($this->targetId)->getCategoryEntries(), $this->entryLink->getCategoryEntries() );
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
         return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'type' => $this->getType(),

            // TODO Check which are needed on front side
            'modifiers' => $this->getModifiers(),
            'constraints' => $this->getConstraints(),
            'profiles' => $this->getProfiles(),
            'category_links' => $this->getCategoryLinks(),
            'selection_entry_groups' => $this->getSelectionEntryGroups(),
            'selection_entries' => $this->getSelectionEntries(),
            'costs' => $this->getCosts(),

            // Non needed because converted to references
            // 'entry_links' => $this->entryLinks,
            // 'info_links' => $this->infoLinks,
        ];
    }
}
