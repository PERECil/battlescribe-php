<?php

declare(strict_types=1);

namespace Battlescribe\Data;

class SharedProfile extends Profile
{
    /** @psalm-var array<string,SharedProfile> */
    private static array $instances = [];

    public function __construct(
        ?TreeInterface $parent,
        Identifier $id,
        string $name,
        ?Identifier $publicationId,
        bool $hidden,
        ?string $typeId,
        ?string $typeName
    )
    {
        parent::__construct($parent, $id, $name, $publicationId, $hidden, $typeId, $typeName);

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
