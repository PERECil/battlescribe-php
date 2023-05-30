<?php

declare(strict_types=1);

namespace Battlescribe\Services\GameSystemDiscovery;

use Battlescribe\Services\Download\DownloadService;
use Battlescribe\Services\ImportRepository\BsrNotFoundException;
use Battlescribe\Services\ImportRepository\DefaultImportRepositoryService;
use Battlescribe\Services\ImportRepository\ImportRepositoryService;
use Battlescribe\Services\ImportRepository\ReleaseNotFoundException;

class DefaultGameSystemDiscoveryService extends GameSystemDiscoveryService
{
    private const INDEX_URL = 'https://api.github.com/users/BSData/repos';

    private DownloadService $downloadService;
    private ImportRepositoryService $importRepositoryService;

    public function __construct(
        DownloadService $downloadService,
        ImportRepositoryService $importRepositoryService
    )
    {
        $this->downloadService = $downloadService;
        $this->importRepositoryService = $importRepositoryService;
    }

    /** @inheritDoc */
    protected function fetchGameSystems(): iterable
    {
        $page = 1;

        while( ($contents = $this->downloadService->download(self::INDEX_URL.'?page='.$page)) !== '[]' ) {

            $jsonData = json_decode($contents);

            foreach($jsonData as $element) {
                if($element->name !== '.github') {
                    try {
                        $repository = $this->importRepositoryService->import($element->name);
                        $gameSystem = $repository->getGameSystem();
                        yield new GameSystemEntry(
                            $element->name,
                            $gameSystem->getName(),
                            $gameSystem->getId()
                        );
                    } catch( ReleaseNotFoundException $exception ) {
                        echo 'Aborting, no release file'.PHP_EOL;
                    } catch( BsrNotFoundException $exception ) {
                        echo 'Aborting, no BSR file in the release'.PHP_EOL;
                    }
                }
            }

            $page++;
        }
    }
}
