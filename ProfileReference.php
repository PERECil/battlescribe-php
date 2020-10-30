<?php

declare(strict_types=1);

namespace Battlescribe;

class ProfileReference implements ProfileInterface
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

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [ 'id' => $this->infoLink->getId(), ] + SharedProfile::get($this->targetId)->jsonSerialize();
    }
}
