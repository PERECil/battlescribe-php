<?php

declare(strict_types=1);


namespace Battlescribe\Services\ImportRelease;

use Battlescribe\Services\Download\DownloadException;
use Battlescribe\Services\Download\DownloadService;
use Monolog\Logger;

class DefaultImportReleaseService implements ImportReleaseService
{
    private DownloadService $downloadService;

    public function __construct(DownloadService $downloadService)
    {
        $this->downloadService = $downloadService;
    }

    public function importRelease(string $repository, string $release = 'latest'): ?Release
    {
        try {
            $data = $this->downloadService->download('https://api.github.com/repos/BSData/'.$repository.'/releases/'.$release);
        } catch( DownloadException $e ) {
            throw $e;
        }

        $json = json_decode($data);

        if($json === null) {
            return null;
        }

        return Release::fromJson($json);
    }
}
