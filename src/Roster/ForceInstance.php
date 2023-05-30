<?php

declare(strict_types=1);

namespace Battlescribe\Roster;

use Battlescribe\Data\BranchTrait;
use Battlescribe\Data\Identifier;
use Battlescribe\Data\IdentifierInterface;

use Battlescribe\Data\TreeInterface;

class ForceInstance implements IdentifierInterface, ForceInterface
{
    use BranchTrait;

    private ForceInterface $implementation;

    /** @var SelectionInstance[] */
    private array $selections;

    /** @var ErrorInterface[] */
    private array $errors;

    private int $selectedCount;
    private ?int $minimumSelectedCount;
    private ?int $maximumSelectedCount;

    public function __construct(?TreeInterface $parent, ForceInterface $implementation)
    {
        $this->parent = $parent;
        $this->implementation = $implementation;

        $this->selections = [];
        $this->rules = [];

        $this->selectedCount = 1;
        $this->minimumSelectedCount = null;
        $this->maximumSelectedCount = null;
    }

    public function getId(): Identifier
    {
        return $this->implementation->getId();
    }

    public function getName(): string
    {
        return $this->implementation->getName();
    }

    public function getCatalogueName(): string
    {
        return $this->implementation->getCatalogueName();
    }

    public function getCategoryLinks(): array
    {
        return $this->implementation->getCategoryLinks();
    }

    /** @inheritDoc */
    public function getChildren(): array
    {
        return array_merge(
            $this->selections,
            $this->rules
        );
    }

    public function getCategories(): array
    {
        return $this->implementation->getCategories();
    }

    public function addSelection(SelectionInterface $selection): void
    {
        $this->selections[] = new SelectionInstance($this, $selection);
    }

    public function getSelections(): array
    {
        return $this->selections;
    }
}
