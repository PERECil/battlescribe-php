<?php

declare(strict_types=1);

namespace Battlescribe\Roster;

use Battlescribe\Data\EntryLink;
use Battlescribe\Data\GameSystem;
use Battlescribe\Data\IdentifierInterface;
use Battlescribe\Data\SelectionEntryGroupInterface;
use Battlescribe\Data\SelectionEntryInterface;
use Battlescribe\Data\TreeInterface;
use Closure;
use UnexpectedValueException;

class Roster implements IdentifierInterface, TreeInterface
{
    private GameSystem $gameSystem;

    /** @var SelectionEntryInstance[] */
    private array $selectionEntries;

    /** @var SelectionEntryGroupInstance[] */
    private array $selectionEntryGroups;

    public function __construct(GameSystem $gameSystem)
    {
        $this->gameSystem = $gameSystem;

        $this->selectionEntries = [];
        $this->selectionEntryGroups = [];

        foreach($this->gameSystem->getEntryLinks() as $entryLink) {
            $this->addEntryLink($entryLink);
        }

        $this->computeState();
    }

    public function getSelectionEntries(): array
    {
        return $this->selectionEntries;
    }

    public function findSelectionEntryByMatcher(Closure $matcher): array
    {
        $result = [];

        foreach($this->getSelectionEntries() as $selectionEntry) {
            if($matcher($selectionEntry)) {
                $result[] = $selectionEntry;
            }

            $result += $selectionEntry->findSelectionEntryByMatcher($matcher);
        }

        return $result;
    }

    /** @return SelectionEntryInstance[] */
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

    public function findSelectionEntryGroupByInstanceId(string $instanceId): ?SelectionEntryGroupInstance
    {
        foreach($this->selectionEntryGroups as $selectionEntryGroup) {
            if($selectionEntryGroup->getInstanceId() === $instanceId) {
                return $selectionEntryGroup;
            }
        }

        foreach($this->selectionEntries as $selectionEntry) {
            $selectionEntryGroup = $selectionEntry->findSelectionEntryGroupByInstanceId($instanceId);

            if($selectionEntryGroup !== null) {
                return $selectionEntryGroup;
            }
        }

        return null;
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
        $this->selectionEntries[] = new SelectionEntryInstance($this, $selectionEntry);
    }

    public function computeState(): void
    {
        foreach($this->selectionEntries as $selectionEntry) {
            $selectionEntry->computeState($this->selectionEntries);
        }
    }

    public function removeSelectionEntry(string $instanceId): void
    {
        foreach( $this->selectionEntries as $index => $selectionEntry) {
            if( $selectionEntry->getInstanceId() === $instanceId) {
                array_splice($this->selectionEntries, $index, 1);
            }
        }
    }

    public function getId(): string
    {
        return '0000-0000-0000-0000';
    }

    public function getSharedId(): string
    {
        return '0000-0000-0000-0000';
    }

    public function getChildren(): array
    {
        return $this->selectionEntries;
    }

    public function getParent(): ?TreeInterface
    {
        return null;
    }

    public function getRoot(): TreeInterface
    {
        return $this;
    }
}
