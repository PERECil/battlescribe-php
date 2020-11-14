<?php

declare(strict_types=1);

namespace Battlescribe;

use JsonSerializable;

interface ProfileInterface extends HideableInterface
{
    public function getId(): string;

    public function getName(): string;

    /** @return Characteristic[] */
    public function getCharacteristics(): array;

    public function getTypeId(): string;

    public function getTypeName(): string;
}
