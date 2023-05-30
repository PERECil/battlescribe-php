<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Closure;

class CategoryEntryReference implements CategoryEntryInterface
{
    private CategoryLink $categoryLink;
    private Identifier $targetId;

    public function __construct(CategoryLink $categoryLink, Identifier $targetId)
    {
        $this->categoryLink = $categoryLink;
        $this->targetId = $targetId;
    }

    public function getId(): Identifier
    {
        return $this->categoryLink->getId();
    }

    public function getSharedId(): Identifier
    {
        return $this->targetId;
    }

    public function isPrimary(): bool
    {
        return $this->categoryLink->isPrimary();
    }

    public function getName(): string
    {
        return SharedCategoryEntry::get($this->targetId)->getName();
    }

    public function isHidden(): bool
    {
        return SharedCategoryEntry::get($this->targetId)->isHidden();
    }

    /** @inheritDoc */
    public function getChildren(): array
    {
        return SharedCategoryEntry::get($this->targetId)->getChildren();
    }

    public function getParent(): ?TreeInterface
    {
        return $this->categoryLink->getParent();
    }

    public function getRoot(): TreeInterface
    {
        return $this->categoryLink->getRoot();
    }

    public function getGameSystem(): ?GameSystem
    {
        return $this->getParent()->getGameSystem();
    }

    public function findByMatcher(Closure $matcher): array
    {
        return SharedCategoryEntry::get($this->targetId)->findByMatcher($matcher);
    }
}
