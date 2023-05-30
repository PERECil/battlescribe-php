<?php

declare(strict_types=1);

namespace Battlescribe\Services\GameSystemDiscovery;

use Battlescribe\Data\Identifier;
use JsonSerializable;
use stdClass;

class GameSystemEntry implements JsonSerializable
{
    private string $repository;
    private string $name;
    private Identifier $gameSystemId;

    public function __construct(string $repository, string $name, Identifier $gameSystemId)
    {
        $this->repository = $repository;
        $this->name = $name;
        $this->gameSystemId = $gameSystemId;
    }

    public function getRepository(): string
    {
        return $this->repository;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getGameSystemId(): Identifier
    {
        return $this->gameSystemId;
    }

    public static function fromJson(stdClass $json): self
    {
        return new self(
            $json->repository,
            $json->name,
            new Identifier($json->game_system_id)
        );
    }

    public function jsonSerialize(): stdClass
    {
        return (object)[
            'repository' => $this->repository,
            'name' => $this->name,
            'game_system_id' => $this->gameSystemId->getValue(),
        ];
    }
}
