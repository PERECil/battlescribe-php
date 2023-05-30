<?php

declare(strict_types=1);

namespace Tests\Battlescribe\Data;

use Battlescribe\Data\Catalog;
use Battlescribe\Data\DataIndex;
use Battlescribe\Data\GameSystem;
use Battlescribe\Data\Identifier;
use Battlescribe\Query\Matcher;
use Battlescribe\Services\ImportRelease\Release;
use Battlescribe\Services\ImportRepository\Repository;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    private static GameSystem $gameSystem;
    private static ?Catalog $asuryani;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $index = DataIndex::fromFile(__DIR__ . '/../Resources/wh40k/v9.7.15/index.xml');

        $repository = Repository::fromDataIndex(new Release('v9.7.15'), $index);

        self::$gameSystem = $repository->getGameSystem();
        self::$asuryani = $repository->findCatalogById(new Identifier('2f0e-dd91-7676-722d'));
    }

    public function testFindPublication(): void
    {
        $id = '1f62-db93-63f4-4dc7';
        $results = self::$asuryani->findByMatcher(Matcher::id($id));

        Assert::assertCount(1, $results);
        Assert::assertSame($id, $results[0]->getId()->__toString());
        Assert::assertSame('Codex: Aeldari', $results[0]->getName());
    }
}
