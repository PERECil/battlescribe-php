<?php

declare(strict_types=1);

namespace Battlescribe;

class SharedSelectionEntryGroup extends SelectionEntryGroup
{
    private static array $instances = [];

    public function __construct(
        string $id,
        string $name,
        bool $hidden,
        bool $collective,
        bool $import,
        ?string $defaultSelectionEntryId
    )
    {
        parent::__construct($id, $name, $hidden, $collective, $import, $defaultSelectionEntryId);

        self::$instances[ $id ] = $this;
    }

    public static function get(string $id): self
    {
        return self::$instances[ $id ];
    }
}
