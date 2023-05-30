<?php

declare(strict_types=1);

namespace Battlescribe\Services\GameSystemDiscovery;

use Battlescribe\Data\Identifier;

class CachedGameSystemDiscoveryService extends GameSystemDiscoveryService
{
    public const CACHE_FILE = __DIR__.'/../../../../../cache/battlescribe/game_systems.json';

    private GameSystemDiscoveryService $underlyingService;

    /** @var GameSystemEntry[] */
    private array $gameSystemEntries;

    public function __construct(GameSystemDiscoveryService $underlyingService)
    {
        $this->underlyingService = $underlyingService;
        $this->gameSystemEntries = [];

        $this->prefetchGameSystems();
    }

    protected function prefetchGameSystems(): void
    {
        $json = null;

        if(file_exists(self::CACHE_FILE)) {
            $data = file_get_contents(self::CACHE_FILE);
            $json = json_decode($data);
        }

        if($json !== null) {
            foreach($json as $element) {
                $gameSystemEntry = GameSystemEntry::fromJson($element);
                $this->gameSystemEntries[$gameSystemEntry->getGameSystemId()->getValue()] = $gameSystemEntry;
            }
        }
    }

    /** @inheritDoc */
    protected function fetchGameSystems(): iterable
    {
        foreach($this->underlyingService->fetchGameSystems() as $gameSystemEntry) {
            $gameSystemEntries[$gameSystemEntry->getGameSystemId()->getValue()] = $gameSystemEntry;
            file_put_contents(self::CACHE_FILE, json_encode($gameSystemEntries));
            yield $gameSystemEntry;
        }
    }

    public function discoverGameSystem(Identifier $gameSystemId): ?GameSystemEntry
    {
        if(array_key_exists($gameSystemId->getValue(), $this->gameSystemEntries)) {
            return $this->gameSystemEntries[$gameSystemId->getValue()];
        }

        return parent::discoverGameSystem($gameSystemId);
    }
}
