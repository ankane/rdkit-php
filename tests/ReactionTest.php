<?php

use Tests\TestCase;

use RDKit\Reaction;

final class ReactionTest extends TestCase
{
    public function testFromSmarts()
    {
        $rxn = Reaction::fromSmarts('[CH3:1][OH:2]>>[CH2:1]=[OH0:2]');
        $this->assertInstanceOf(Reaction::class, $rxn);
    }

    public function testFromSmartsInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('invalid input');

        Reaction::fromSmarts('?');
    }

    public function testToSvg()
    {
        $rxn = Reaction::fromSmarts('[CH3:1][OH:2]>>[CH2:1]=[OH0:2]');
        $svg = $rxn->toSvg();
        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString("width='250px'", $svg);
        $this->assertStringContainsString("height='200px'", $svg);
    }

    public function testToSvgWidthHeight()
    {
        $rxn = Reaction::fromSmarts('[CH3:1][OH:2]>>[CH2:1]=[OH0:2]');
        $svg = $rxn->toSvg(width: 500, height: 400);
        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString("width='500px'", $svg);
        $this->assertStringContainsString("height='400px'", $svg);
    }

    public function testClone()
    {
        $rxn = Reaction::fromSmarts('[CH3:1][OH:2]>>[CH2:1]=[OH0:2]');
        $rxn2 = clone $rxn;
        $this->assertEquals($rxn->toSvg(), $rxn2->toSvg());
    }
}
