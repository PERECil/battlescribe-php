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

    public function getTypeId(): string
    {
        return SharedProfile::get($this->targetId)->getTypeId();
    }

    public function getTypeName(): string
    {
        return SharedProfile::get($this->targetId)->getTypeName();
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [ 'id' => $this->infoLink->getId(), ] + SharedProfile::get($this->targetId)->jsonSerialize();
    }
}
