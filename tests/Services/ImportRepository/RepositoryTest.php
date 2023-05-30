<?php

declare(strict_types=1);

namespace Tests\Battlescribe\Services\ImportRepository;

use Battlescribe\Data\DataIndex;
use Battlescribe\Query\Matcher;
use Battlescribe\Services\ImportRelease\Release;
use Battlescribe\Services\ImportRepository\Repository;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class RepositoryTest extends TestCase
{
    private static Repository $repository;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $index = DataIndex::fromFile(__DIR__ . '/../../Resources/wh40k/v9.7.15/index.xml');

        self::$repository = Repository::fromDataIndex(new Release('v9.7.15'), $index);
    }

    public function testFindPublication(): void
    {
        $id = '1f62-db93-63f4-4dc7';
        $results = self::$repository->findByMatcher(Matcher::id($id));

        Assert::assertCount(1, $results);
        Assert::assertSame($id, $results[0]->getId()->__toString());
        Assert::assertSame('Codex: Aeldari', $results[0]->getName());
    }
}
