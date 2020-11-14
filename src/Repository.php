<?php

declare(strict_types=1);

namespace Battlescribe;

use UnexpectedValueException;

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

    public static function fromDataIndex(DataIndex $index): Repository
    {
        $gameSystem = null;
        $catalogs = [];

        foreach($index->getDataIndexEntries() as $dataIndexEntry) {
            switch($dataIndexEntry->getType()) {
                case DataIndexEntryType::CATALOG:
                    $catalogs[] = Catalog::fromFile( $index->getBasePath().'/'.$dataIndexEntry->getFilePath() );
                    break;
                case DataIndexEntryType::GAME_SYSTEM:
                    $gameSystem = GameSystem::fromFile($index->getBasePath().'/'.$dataIndexEntry->getFilePath());
                    break;
                default:
                    throw new UnexpectedValueException( 'Unhandled data index entry type "'.$dataIndexEntry->getType().'"');
            }
        }

        if($gameSystem === null) {
            throw new UnexpectedValueException( 'Missing game system in index' );
        }

        return new Repository($index, $gameSystem, $catalogs);
    }
}
