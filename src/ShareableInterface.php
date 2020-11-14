<?php

declare(strict_types=1);


namespace Battlescribe;

interface ShareableInterface
{
    public function getSharedId(): string;
}
