<?php

namespace Tests\Battlescribe\Data;

use Battlescribe\Data\Identifier;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/** @covers \Battlescribe\Data\Identifier */
class IdentifierTest extends TestCase
{
    /** @covers \Battlescribe\Data\Identifier::fromString() */
    public function testFromString(): void
    {
        $identifier = Identifier::fromString( "1ad7-dc0f-9fd9-78e9::903e-06e8-ebbc-4d03::a42f-aef2-3d38-3a39" );

        Assert::assertEquals("1ad7-dc0f-9fd9-78e9", $identifier->getValue());
        Assert::assertEquals("903e-06e8-ebbc-4d03", $identifier->getChild()->getValue());
        Assert::assertEquals("a42f-aef2-3d38-3a39", $identifier->getChild()->getChild()->getValue());
        Assert::assertNull($identifier->getChild()->getChild()->getChild());
    }
}
