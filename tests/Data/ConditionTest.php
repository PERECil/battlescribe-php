<?php

declare(strict_types=1);

namespace Tests\Battlescribe\Data;

use Battlescribe\Data\Catalog;
use Battlescribe\Data\DataIndex;
use Battlescribe\Data\ForceEntry;
use Battlescribe\Data\GameSystem;
use Battlescribe\Data\Identifier;
use Battlescribe\Data\TreeInterface;
use Battlescribe\Query\Matcher;
use Battlescribe\Roster\ForceInterface;
use Battlescribe\Roster\Roster;
use Battlescribe\Roster\RosterInstance;
use Battlescribe\Services\ImportRelease\Release;
use Battlescribe\Services\ImportRepository\Repository;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class ConditionTest extends TestCase
{
    private static GameSystem $gameSystem;
    private static ?Catalog $asuryani;

    private RosterInstance $roster;
    private ForceInterface $force;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $index = DataIndex::fromFile(__DIR__ . '/../Resources/wh40k-killteam/v1.4.29/index.xml');

        $repository = Repository::fromDataIndex(new Release('v1.4.29'), $index);

        self::$gameSystem = $repository->getGameSystem();
        self::$asuryani = $repository->findCatalogById(new Identifier('a796-a947-905e-205c'));
    }

    public function setUp(): void
    {
        $this->roster = new RosterInstance( self::$gameSystem, 'test');
        $forces = self::$gameSystem->findByMatcher(Matcher::allOf(Matcher::type(ForceEntry::class), Matcher::name('Kill Team List')));

        $this->roster->addForce($forces[0]);
    }

    public function testNonSpecialistGuardianDefenderSpecialism()
    {
        $nonSpecialistGuardianDefender = self::$asuryani->findSelectionEntry(new Identifier('38fe-f863-513a-9012'));

        $this->roster->getForces()[0]->addSelectionEntry($nonSpecialistGuardianDefender);

        $this->roster->computeState();

        $entries = $this->roster->findByMatcher(fn(TreeInterface $e) => $e->getId()->equals(new Identifier('38fe-f863-513a-9012')));

        foreach( $entries as $entry ) {
            foreach( $entry->findSelectionEntryGroupByName('Specialism') as $specialism) {
                Assert::assertTrue($specialism->isHidden());
            }
        }
    }

    public function testSpecialistGuardianDefenderSpecialism()
    {
        $specialistGuardianDefender = self::$asuryani->findSelectionEntry(Identifier::fromString('1b66-3fce-1a28-c804'));

        $this->roster->getForces()[0]->addSelectionEntry($specialistGuardianDefender);

        $this->roster->computeState();

        $entries = $this->roster->findByMatcher(fn(TreeInterface $e) => $e->getId()->equals(new Identifier('1b66-3fce-1a28-c804')));

        foreach( $entries as $entry ) {
            foreach( $entry->findSelectionEntryGroupByName('Specialism') as $specialism) {
                Assert::assertFalse($specialism->isHidden());

                foreach($specialism->getSelectionEntries() as $selectionEntry) {
                    Assert::assertSame(!in_array( $selectionEntry->getName(), [ 'Comms', 'Medic', 'Scout', 'Veteran' ] ), $selectionEntry->isHidden(), $selectionEntry->getName().' (with an id of '.$selectionEntry->getId().') must be hidden');
                }
            }
        }
    }

    public function testLeaderGuardianDefenderSpecialism()
    {
        $specialistGuardianDefender = self::$asuryani->findSelectionEntry(Identifier::fromString('3f2a-5c3b-00ba-68aa'));

        $this->roster->getForces()[0]->addSelectionEntry($specialistGuardianDefender);

        $this->roster->computeState();

        $entries = $this->roster->findByMatcher(fn(TreeInterface $e) => $e->getId()->equals(new Identifier('3f2a-5c3b-00ba-68aa')));

        foreach( $entries as $entry ) {
            foreach( $entry->findSelectionEntryGroupByName('Specialism') as $specialism) {
                Assert::assertFalse($specialism->isHidden());

                foreach($specialism->getSelectionEntries() as $selectionEntry) {
                    Assert::assertSame(!in_array( $selectionEntry->getName(), [ 'Leader' ] ), $selectionEntry->isHidden(), $selectionEntry->getName().' (with an id of '.$selectionEntry->getId().') must be hidden');
                }
            }
        }
    }
}
