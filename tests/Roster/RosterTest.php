<?php

declare(strict_types=1);

namespace Tests\Battlescribe\Roster;

use Battlescribe\Data\Catalog;
use Battlescribe\Data\DataIndex;
use Battlescribe\Data\GameSystem;
use Battlescribe\Data\Repository;
use Battlescribe\Roster\Roster;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class RosterTest extends TestCase
{
    private static GameSystem $gameSystem;
    private static ?Catalog $asuryani;

    private Roster $roster;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $index = DataIndex::fromFile(__DIR__ . '/../Resources/wh40k-killteam/v1.4.29/index.xml');

        $repository = Repository::fromDataIndex($index);

        self::$gameSystem = $repository->getGameSystem();
        self::$asuryani = $repository->findCatalogById('a796-a947-905e-205c');
    }

    public function setUp(): void
    {
        $this->roster = new Roster(self::$gameSystem);
    }

    public function testInitialRosterShouldHaveListConfiguration(): void
    {
        $listConfiguration = $this->roster->findSelectionEntryByName('List Configuration');

        Assert::assertNotNull($listConfiguration);
        Assert::assertCount(1, $listConfiguration);
        Assert::assertFalse($listConfiguration[0]->isHidden());
    }

    public function testInitialRosterShouldHaveMatchedPlaySelected(): void
    {
        $listConfiguration = $this->roster->findSelectionEntryByName('List Configuration');

        Assert::assertNotNull($listConfiguration);
        Assert::assertCount(1, $listConfiguration);
        Assert::assertSame('Matched Play: Kill Team', $listConfiguration[0]->getSelectionEntryGroups()[0]->getSelectedEntry()->getName());
    }

    public function testInitialRosterShouldHaveResourcesHidden(): void
    {
        $resources = $this->roster->findSelectionEntryByName('Resources');

        Assert::assertNotNull($resources);
        Assert::assertCount(1, $resources);
        Assert::assertTrue($resources[0]->isHidden());
    }

    public function testRosterWithCampaignConfigurationShouldShowResources(): void
    {
        $listConfiguration = $this->roster->findSelectionEntryByName('List Configuration');

        $campaign = $listConfiguration[0]->getSelectionEntryGroups()[0]->findSelectionEntryByName('Campaign: Kill Team')[0];

        $listConfiguration[0]->getSelectionEntryGroups()[0]->setSelectedInstanceId($campaign->getInstanceId());

        Assert::assertNotNull($listConfiguration);
        Assert::assertCount(1, $listConfiguration);
        Assert::assertSame('Campaign: Kill Team', $listConfiguration[0]->getSelectionEntryGroups()[0]->getSelectedEntry()->getName());

        $resources = $this->roster->findSelectionEntryByName('Resources');

        Assert::assertNotNull($resources);
        Assert::assertCount(1, $resources);
        Assert::assertFalse($resources[0]->isHidden());
    }
}
