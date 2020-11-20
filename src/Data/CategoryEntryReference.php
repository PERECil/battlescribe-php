<?php

declare(strict_types=1);

namespace Battlescribe\Data;

class CategoryEntryReference implements CategoryEntryInterface
{
    private CategoryLink $categoryLink;
    private string $targetId;

    public function __construct(CategoryLink $categoryLink, string $targetId)
    {
        $this->categoryLink = $categoryLink;
        $this->targetId = $targetId;
    }

    public function getId(): string
    {
        return $this->categoryLink->getId();
    }

    public function getSharedId(): string
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
}
