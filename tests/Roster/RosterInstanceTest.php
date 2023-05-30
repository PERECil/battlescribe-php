<?php

declare(strict_types=1);

namespace Tests\Battlescribe\Roster;

use Battlescribe\Data\Catalog;
use Battlescribe\Data\DataIndex;
use Battlescribe\Data\ForceEntry;
use Battlescribe\Data\GameSystem;
use Battlescribe\Data\Identifier;
use Battlescribe\Query\Matcher;
use Battlescribe\Roster\Roster;
use Battlescribe\Roster\RosterInstance;
use Battlescribe\Services\ImportRelease\Release;
use Battlescribe\Services\ImportRepository\Repository;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class RosterInstanceTest extends TestCase
{
    private static GameSystem $gameSystem;
    private static ?Catalog $asuryani;

    private RosterInstance $roster;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $index = DataIndex::fromFile(__DIR__ . '/../Resources/wh40k-killteam/v1.4.29/index.xml');

        $repository = Repository::fromDataIndex(new Release('v1.4.29'), $index);

        self::$gameSystem = $repository->getGameSystem();
        self::$asuryani = $repository->findCatalogById(Identifier::fromString('a796-a947-905e-205c'));
    }

    public function setUp(): void
    {
        $this->roster = RosterInstance::fromGameSystem(self::$gameSystem, 'test');

        $forces = self::$gameSystem->findByMatcher(Matcher::allOf(Matcher::type(ForceEntry::class), Matcher::name('Kill Team List')));

        $this->roster->addForce($forces[0]);
    }

    public function testInitialRosterShouldHaveListConfiguration(): void
    {
        $listConfiguration = $this->roster->findByMatcher(Matcher::name('List Configuration'));

        Assert::assertNotNull($listConfiguration);
        Assert::assertCount(1, $listConfiguration);
        Assert::assertFalse($listConfiguration[0]->isHidden());
    }

    public function testInitialRosterShouldHaveMatchedPlaySelected(): void
    {
        $listConfiguration = $this->roster->findByMatcher(Matcher::name('List Configuration'));

        Assert::assertNotNull($listConfiguration);
        Assert::assertCount(1, $listConfiguration);
        Assert::assertSame('Matched Play: Kill Team', $listConfiguration[0]->getSelectionEntryGroups()[0]->getSelectedEntry()->getName());

        Assert::assertSame( 1, $listConfiguration[0]->getSelectedCount());
    }

    public function testInitialRosterShouldHaveResourcesHidden(): void
    {
        $resources = $this->roster->findByMatcher(Matcher::name('Resources'));

        Assert::assertNotNull($resources);
        Assert::assertCount(1, $resources);
        Assert::assertTrue($resources[0]->isHidden());
    }

    public function testRosterWithCampaignConfigurationShouldShowResources(): void
    {
        $listConfiguration = $this->roster->findByMatcher(Matcher::name('List Configuration'));

        $campaign = $listConfiguration[0]->getSelectionEntryGroups()[0]->findSelectionEntryByName('Campaign: Kill Team')[0];

        $listConfiguration[0]->getSelectionEntryGroups()[0]->setSelectedInstanceId($campaign->getInstanceId());

        Assert::assertNotNull($listConfiguration);
        Assert::assertCount(1, $listConfiguration);
        Assert::assertSame('Campaign: Kill Team', $listConfiguration[0]->getSelectionEntryGroups()[0]->getSelectedEntry()->getName());

        $resources = $this->roster->findByMatcher(Matcher::name('Resources'));

        Assert::assertNotNull($resources);
        Assert::assertCount(1, $resources);
        Assert::assertFalse($resources[0]->isHidden());

        Assert::assertSame( null, $resources[0]->getMinimumSelectedCount());
        Assert::assertSame( 1, $resources[0]->getMaximumSelectedCount());
        Assert::assertSame( 1, $resources[0]->getSelectedCount());

        // Ensure that 0 resource is selected, the min is 0 and there is no maximum
        foreach($resources[0]->getSelectionEntries() as $selectionEntry) {
            Assert::assertSame(1, $selectionEntry->getMinimumSelectedCount());
            Assert::assertSame(null, $selectionEntry->getMaximumSelectedCount());
            Assert::assertSame(1, $selectionEntry->getSelectedCount());
        }
    }
}
