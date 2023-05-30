<?php

declare(strict_types=1);

namespace Tests\Battlescribe\Roster;

use Battlescribe\Data\CostInterface;
use Battlescribe\Data\TreeInterface;
use Battlescribe\Roster\Selection;
use Battlescribe\Utils\SimpleXmlElementFacade;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class SelectionTest extends TestCase
{
    public function getFromXmlData(): iterable
    {

    }

    public function testFromXml(): void
    {
        $xml = simplexml_load_file(__DIR__.'/Payloads/Selection/sample1.xml');

        $facade = new SimpleXmlElementFacade($xml);

        $selection = Selection::fromXml(null, $facade);

        $costs = $selection->findByMatcher(fn(TreeInterface $e) => $e instanceof CostInterface);

        Assert::assertNotEmpty($costs);
    }
}
