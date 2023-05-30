<?php

declare(strict_types=1);

namespace Battlescribe\Services\GameSystemDiscovery;

use Battlescribe\Data\Identifier;

abstract class GameSystemDiscoveryService
{
    /** @return GameSystemEntry[] */
    protected abstract function fetchGameSystems(): iterable;

    public function discoverGameSystem(Identifier $gameSystemId): ?GameSystemEntry
    {
        foreach($this->fetchGameSystems() as $gameSystemEntry) {
            if($gameSystemEntry->getGameSystemId()->equals($gameSystemId)) {
                return $gameSystemEntry;
            }
        }

        return null;
    }
}
