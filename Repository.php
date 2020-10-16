<?php

declare(strict_types=1);

namespace BattleScribe;

class Repository
{
    private DataIndex $dataIndex;
    private GameSystem $gameSystem;

    /** @var Catalog[] */
    private array $catalogs;

    public function __construct(DataIndex $dataIndex, GameSystem $gameSystem, array $catalogs = [])
    {
        $this->dataIndex = $dataIndex;
        $this->gameSystem = $gameSystem;
        $this->catalogs = $catalogs;
    }

    public function getDataIndex(): DataIndex
    {
        return $this->dataIndex;
    }

    public function getGameSystem(): GameSystem
    {
        return $this->gameSystem;
    }

    public function getCatalogs(): array
    {
        return $this->catalogs;
    }

    public function addCatalog(Catalog $catalog): void
    {
        $this->catalogs[] = $catalog;
    }

    public function findCatalog(string $id): ?Catalog
    {
        foreach($this->catalogs as $catalog) {
            if($catalog->getId() === $id) {
                return $catalog;
            }
        }

        return null;
    }
}
