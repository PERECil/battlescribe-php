<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Exception;

class SharedRule extends Rule
{
    /** @var array<string,SharedRule> */
    private static array $instances = [];

    public function __construct(
        ?TreeInterface $parent,
        Identifier $id,
        string $name,
        string $publicationId,
        bool $hidden,
        ?string $description
    )
    {
        parent::__construct($parent, $id, $name, $publicationId, $hidden, $description);

        self::$instances[ $id->getValue() ] = $this;
    }

    public function __wakeup(): void
    {
        self::$instances[ $this->getId()->getValue() ] = $this;
    }

    public static function get(Identifier $id): ?self
    {
        return self::$instances[ $id->getValue() ] ?? null;
    }
}
