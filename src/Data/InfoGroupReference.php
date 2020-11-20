<?php

declare(strict_types=1);

namespace Battlescribe\Data;

class InfoGroupReference implements InfoGroupInterface
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

    public function isHidden(): bool
    {
        return SharedInfoGroup::get($this->targetId)->isHidden();
    }

    public function getName(): string
    {
        return SharedInfoGroup::get($this->targetId)->getName();
    }
}
