<?php

declare(strict_types=1);


namespace Battlescribe\Data;

use UnexpectedValueException;

trait EntryLinkTrait
{
    /** @var EntryLink[] */
    private array $entryLinks;

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

    public abstract function addSelectionEntryGroup(SelectionEntryGroupInterface $selectionEntryGroup): void;

    public abstract function addSelectionEntry(SelectionEntryInterface $selectionEntry): void;
}
