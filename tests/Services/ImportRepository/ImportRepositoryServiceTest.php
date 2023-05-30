<?php

namespace Tests\Battlescribe\Services\ImportRepository;

use Battlescribe\Services\Download\DownloadService;
use Battlescribe\Services\Download\OAuthCredentials;
use Battlescribe\Services\ImportRelease\Asset;
use Battlescribe\Services\ImportRelease\ImportReleaseService;
use Battlescribe\Services\ImportRelease\Release;
use Battlescribe\Services\ImportRepository\DefaultImportRepositoryService;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class ImportRepositoryServiceTest extends TestCase
{
    public function setUp(): void
    {
        $this->downloadService = $this->createMock(DownloadService::class);
        $this->importReleaseService = $this->createMock(ImportReleaseService::class);

        $this->service = new DefaultImportRepositoryService(
            $this->downloadService,
            $this->importReleaseService
        );
    }

    public function testImport(): void
    {
        $repositoryId = 'wh40k';
        $releaseId = 'v9.2.17';
        $assetUrl = 'https://example.com/';

        $payload = file_get_contents(__DIR__.'/Payloads/53703968.json');

        $asset = $this->createMock(Asset::class);
        $asset->expects(TestCase::atLeastOnce())->method('getUrl')->willReturn($assetUrl);

        $release = $this->createMock(Release::class);
        $release->expects(TestCase::atLeastOnce())->method('getTag')->willReturn($releaseId);
        $release->expects(TestCase::once())->method('findAsset')->with('*.bsr')->willReturn([$asset]);

        $this->importReleaseService->expects(TestCase::once())->method('importRelease')->with($repositoryId, $releaseId)->willReturn($release);
        $this->downloadService->expects(TestCase::once())->method('download')->willReturn($payload);

        $repository = $this->service->import($repositoryId, $releaseId);

        Assert::assertSame('28ec-711c-d87f-3aeb', $repository->getGameSystem()->getId());
    }
}
