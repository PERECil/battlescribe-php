<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Closure;

class ProfileReference implements ProfileInterface
{
    private InfoLink $infoLink;
    private Identifier $targetId;

    public function __construct(InfoLink $infoLink, Identifier $targetId)
    {
        $this->infoLink = $infoLink;
        $this->targetId = $targetId;
    }

    public function getId(): Identifier
    {
        return $this->infoLink->getId();
    }

    public function getName(): string
    {
        return SharedProfile::get($this->targetId)->getName();
    }

    public function getCharacteristics(): array
    {
        return SharedProfile::get($this->targetId)->getCharacteristics();
    }

    public function isHidden(): bool
    {
        return SharedProfile::get($this->targetId)->isHidden();
    }

    public function getTypeId(): ?string
    {
        return SharedProfile::get($this->targetId)->getTypeId();
    }

    public function getTypeName(): ?string
    {
        return SharedProfile::get($this->targetId)->getTypeName();
    }

    /** @inheritDoc */
    public function getChildren(): array
    {
        return SharedProfile::get($this->targetId)->getChildren();
    }

    public function getParent(): ?TreeInterface
    {
        return $this->infoLink->getParent();
    }

    public function getRoot(): TreeInterface
    {
        return $this->infoLink->getRoot();
    }

    public function getGameSystem(): ?GameSystem
    {
        return $this->getParent()->getGameSystem();
    }

    public function findByMatcher(Closure $matcher): array
    {
        return SharedProfile::get($this->targetId)?->findByMatcher($matcher) ?? [];
    }
}
