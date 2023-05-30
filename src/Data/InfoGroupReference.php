<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Closure;

class InfoGroupReference implements InfoGroupInterface
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

    public function isHidden(): bool
    {
        return SharedInfoGroup::get($this->targetId)->isHidden();
    }

    public function getName(): string
    {
        return SharedInfoGroup::get($this->targetId)->getName();
    }

    public function getParent(): ?TreeInterface
    {
        return $this->infoLink->getParent();
    }

    public function getChildren(): array
    {
        return $this->infoLink->getChildren();
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
        return $this->infoLink->findByMatcher($matcher);
    }
}
