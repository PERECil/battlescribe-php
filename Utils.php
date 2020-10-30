<?php

declare(strict_types=1);


namespace BattleScribe;

class Utils
{
    public static function isId(string $candidate): bool
    {
        return preg_match('%[a-z0-9]{4}\-[a-z0-9]{4}\-[a-z0-9]{4}\-[a-z0-9]{4}%', $candidate ) === 1;
    }
}
