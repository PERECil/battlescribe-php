<?php

declare(strict_types=1);

namespace Battlescribe\Roster;

use Battlescribe\Data\BranchTrait;
use Battlescribe\Data\Identifier;
use Battlescribe\Data\IdentifierInterface;
use Battlescribe\Data\TreeInterface;

class SelectionInstance implements IdentifierInterface, TreeInterface, SelectionInterface
{
    use BranchTrait;

    private SelectionInterface $implementation;

    public function __construct(?TreeInterface $parent, SelectionInterface $implementation)
    {
        $this->parent = $parent;
        $this->implementation = $implementation;
    }

    public function getName(): string
    {
        return $this->implementation->getName();
    }

    public function getId(): Identifier
    {
        return $this->implementation->getId();
    }

    public function getChildren(): array
    {
        return $this->implementation->getChildren();
    }

    public function getSelections(): array
    {
        return $this->implementation->getSelections();
    }

    public function hasCategory(Category $category): bool
    {
        return $this->implementation->hasCategory($category);
    }

    /** @inheritDoc */
    public function getCategories(): array
    {
        return $this->implementation->getCategories();
    }
}
