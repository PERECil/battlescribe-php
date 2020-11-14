<?php

declare(strict_types=1);

namespace Tests\Battlescribe;

use App\Models\Roster;
use Battlescribe\Catalog;
use Battlescribe\DataIndex;
use Battlescribe\Repository;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class ConditionTest extends TestCase
{
    private static ?Catalog $asuryani;

    private Roster $roster;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $index = DataIndex::fromFile(__DIR__.'/Resources/wh40k-killteam/v1.4.29/index.xml');

        $repository = Repository::fromDataIndex($index);

        self::$asuryani = $repository->findCatalog('a796-a947-905e-205c');


    }

    public function setUp(): void
    {
        $this->roster = new Roster();
    }

    public function testNonSpecialistGuardianDefenderSpecialism()
    {
        $nonSpecialistGuardianDefender = self::$asuryani->findSelectionEntry('38fe-f863-513a-9012');

        $this->roster->addSelectionEntry($nonSpecialistGuardianDefender);

        $this->roster->computeState();

        $entries = $this->roster->findSelectionEntries('38fe-f863-513a-9012');

        foreach( $entries as $entry ) {
            foreach( $entry->findSelectionEntryGroupByName('Specialism') as $specialism) {
                Assert::assertTrue($specialism->isHidden());
            }
        }
    }

    public function testSpecialistGuardianDefenderSpecialism()
    {
        $specialistGuardianDefender = self::$asuryani->findSelectionEntry('1b66-3fce-1a28-c804');

        $this->roster->addSelectionEntry($specialistGuardianDefender);

        $this->roster->computeState();

        $entries = $this->roster->findSelectionEntries('1b66-3fce-1a28-c804');

        foreach( $entries as $entry ) {
            foreach( $entry->findSelectionEntryGroupByName('Specialism') as $specialism) {
                Assert::assertFalse($specialism->isHidden());

                foreach($specialism->getSelectionEntries() as $selectionEntry) {
                    Assert::assertSame(!in_array( $selectionEntry->getName(), [ 'Comms', 'Medic', 'Scout', 'Veteran' ] ), $selectionEntry->isHidden());
                }
            }
        }
    }

    public function testLeaderGuardianDefenderSpecialism()
    {
        $specialistGuardianDefender = self::$asuryani->findSelectionEntry('3f2a-5c3b-00ba-68aa');

        $this->roster->addSelectionEntry($specialistGuardianDefender);

        $this->roster->computeState();

        $entries = $this->roster->findSelectionEntries('3f2a-5c3b-00ba-68aa');

        foreach( $entries as $entry ) {
            foreach( $entry->findSelectionEntryGroupByName('Specialism') as $specialism) {
                Assert::assertFalse($specialism->isHidden());

                foreach($specialism->getSelectionEntries() as $selectionEntry) {
                    Assert::assertSame(!in_array( $selectionEntry->getName(), [ 'Leader' ] ), $selectionEntry->isHidden());
                }
            }
        }
    }
}
