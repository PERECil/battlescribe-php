<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Closure;

class RuleReference implements RuleInterface
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
        return SharedRule::get($this->targetId)->getName();
    }

    public function getDescription(): ?string
    {
        return SharedRule::get($this->targetId)->getDescription();
    }

    /** @inheritDoc */
    public function getChildren(): array
    {
        return SharedRule::get($this->targetId)->getChildren();
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
        return SharedRule::get($this->targetId)?->findByMatcher($matcher) ?? [];
    }
}
