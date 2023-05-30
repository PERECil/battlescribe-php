<?php

declare(strict_types=1);


namespace Battlescribe\Roster;

use Battlescribe\Data\GameSystem;
use Battlescribe\Data\Identifier;
use Battlescribe\Data\Publication;
use Battlescribe\Data\RuleInterface;
use Battlescribe\Data\TreeInterface;
use Closure;

class RuleInstance implements RuleInterface
{
    private Publication $publication;
    private RuleInterface $implementation;

    public function __construct(Publication $publication, RuleInterface $implementation)
    {
        $this->publication = $publication;
        $this->implementation = $implementation;
    }

    public function getId(): Identifier
    {
        return $this->implementation->getId();
    }

    public function getName(): string
    {
        return $this->implementation->getName();
    }

    public function getDescription(): string
    {
        return $this->implementation->getDescription();
    }

    public function getPublication(): Publication
    {
        return $this->publication;
    }

    /** @inheritDoc */
    public function getChildren(): array
    {
        return $this->implementation->getChildren();
    }

    public function getGameSystem(): ?GameSystem
    {
        return $this?->getParent()->getGameSystem();
    }

    public function getParent(): ?TreeInterface
    {
        return $this->implementation->getParent();
    }

    public function getRoot(): TreeInterface
    {
        return $this->implementation->getRoot();
    }

    /** @inheritDoc */
    public function findByMatcher(Closure $matcher): array
    {
        $result = array_filter($this->getChildren(), $matcher);

        /** @var TreeInterface $child */
        foreach($this->getChildren() as $child) {
            $result = array_merge($result, $child->findByMatcher($matcher));
        }

        return $result;
    }
}
