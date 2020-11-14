<?php

declare(strict_types=1);


namespace Battlescribe;

class RuleReference implements RuleInterface
{
    private InfoLink $infoLink;
    private string $targetId;

    public function __construct(InfoLink $infoLink, string $targetId)
    {
        $this->infoLink = $infoLink;
        $this->targetId = $targetId;
    }

    public function getId(): string
    {
        return $this->infoLink->getId();
    }

    public function getName(): string
    {
        return SharedRule::get($this->targetId)->getName();
    }
}
