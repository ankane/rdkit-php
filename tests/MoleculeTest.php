<?php

use Tests\TestCase;

use RDKit\Molecule;

final class MoleculeTest extends TestCase
{
    public function testFromSmiles()
    {
        $mol = Molecule::fromSmarts('CCO');
        $this->assertInstanceOf(Molecule::class, $mol);
    }

    public function testFromSmilesInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('invalid input');

        Molecule::fromSmiles('?');
    }

    public function testFromSmarts()
    {
        $mol = Molecule::fromSmarts('ccO');
        $this->assertInstanceOf(Molecule::class, $mol);
    }

    public function testFromSmartsInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('invalid input');

        Molecule::fromSmarts('?');
    }

    public function testNumAtoms()
    {
        $mol = Molecule::fromSmiles('CCO');
        $this->assertEquals(3, $mol->numAtoms());

        $mol = Molecule::fromSmiles('[H]OC([H])([H])C([H])([H])[H]', removeHs: false);
        $this->assertEquals(9, $mol->numAtoms());
    }

    public function testNumHeavyAtoms()
    {
        $mol = Molecule::fromSmiles('CCO');
        $this->assertEquals(3, $mol->numHeavyAtoms());
    }

    public function testMatch()
    {
        $mol = Molecule::fromSmiles('c1ccccc1O');
        $pattern = Molecule::fromSmarts('ccO');
        $this->assertTrue($mol->hasMatch($pattern));
        $this->assertEquals([[0, 5, 6], [4, 5, 6]], $mol->match($pattern));
    }

    public function testMatchNone()
    {
        $mol = Molecule::fromSmiles('c1ccccc1O');
        $pattern = Molecule::fromSmarts('ccOc');
        $this->assertFalse($mol->hasMatch($pattern));
        $this->assertEmpty($mol->match($pattern));
    }

    public function testMatchFromSmiles()
    {
        $mol = Molecule::fromSmiles('C1=CC=CC=C1OC');
        $this->assertTrue($mol->hasMatch(Molecule::fromSmiles('COC')));
        $this->assertFalse($mol->hasMatch(Molecule::fromSmarts('COC')));
        $this->assertTrue($mol->hasMatch(Molecule::fromSmarts('COc')));
    }

    public function testMatchUseChirality()
    {
        $mol = Molecule::fromSmiles('CC[C@H](F)Cl');
        $this->assertFalse($mol->hasMatch(Molecule::fromSmiles('C[C@@H](F)Cl'), useChirality: true));
    }

    public function testMatchInvalidPattern()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected RDKit\Molecule');

        $mol = Molecule::fromSmiles('c1ccccc1O');
        $mol->match('COC');
    }

    public function testFragments()
    {
        $mol = Molecule::fromSmiles('n1ccccc1.CC(C)C.OCCCN');
        $expected = ['c1ccncc1', 'CC(C)C', 'NCCCO'];
        $this->assertEquals($expected, array_map(fn ($v) => $v->toSmiles(), $mol->fragments()));
    }

    public function testFragmentsSanitize()
    {
        $mol = Molecule::fromSmiles('N(C)(C)(C)C.c1ccc1', sanitize: false);
        $expected = ['CN(C)(C)C', 'c1ccc1'];
        $this->assertEquals($expected, array_map(fn ($v) => $v->toSmiles(), $mol->fragments(sanitize: false)));
    }

    public function testRdkitFingerprint()
    {
        $mol = Molecule::fromSmiles('Cc1ccccc1');
        $this->assertEquals(2048, strlen($mol->rdkitFingerprint()));
    }

    public function testMorganFingerprint()
    {
        $mol = Molecule::fromSmiles('Cc1ccccc1');
        $this->assertEquals(2048, strlen($mol->morganFingerprint()));
    }

    public function testMorganFingerprintLength()
    {
        $mol = Molecule::fromSmiles('Cc1ccccc1');
        $this->assertEquals(1024, strlen($mol->morganFingerprint(length: 1024)));
    }

    public function testMorganFingerprintRadiusZero()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('radius must be greater than 0');

        $mol = Molecule::fromSmiles('Cc1ccccc1');
        $mol->morganFingerprint(radius: 0);
    }

    public function testPatternFingerprint()
    {
        $mol = Molecule::fromSmiles('Cc1ccccc1');
        $this->assertEquals(2048, strlen($mol->patternFingerprint()));
    }

    public function testTopologicalTorsionFingerprint()
    {
        $mol = Molecule::fromSmiles('Cc1ccccc1');
        $this->assertEquals(2048, strlen($mol->TopologicalTorsionFingerprint()));
    }

    public function testAtomPairFingerprint()
    {
        $mol = Molecule::fromSmiles('Cc1ccccc1');
        $this->assertEquals(2048, strlen($mol->atomPairFingerprint()));
    }

    public function testMaccsFingerprint()
    {
        $mol = Molecule::fromSmiles('Cc1ccccc1');
        $this->assertEquals(166, strlen($mol->maccsFingerprint()));
    }

    public function testAddHs()
    {
        $mol = Molecule::fromSmiles('CCO');
        $mol->addHs();
        $this->assertEquals('[H]OC([H])([H])C([H])([H])[H]', $mol->toSmiles());
    }

    public function testRemoveHs()
    {
        $mol = Molecule::fromSmiles('[H]OC([H])([H])C([H])([H])[H]', removeHs: false);
        $mol->removeHs();
        $this->assertEquals('CCO', $mol->toSmiles());
    }

    public function testCleanup()
    {
        $mol = Molecule::fromSmiles('[Pt]CCN(=O)=O', sanitize: false);
        $mol->cleanup();
        $this->assertEquals('[CH2-]C[N+](=O)[O-].[Pt+]', $mol->toSmiles());
    }

    public function testNormalize()
    {
        $mol = Molecule::fromSmiles('[CH2-]CN(=O)=O', sanitize: false);
        $mol->normalize();
        $this->assertEquals('[CH2-]C[N+](=O)[O-]', $mol->toSmiles());
    }

    public function testNeutralize()
    {
        $mol = Molecule::fromSmiles('[CH2-]CN(=O)=O', sanitize: false);
        $mol->neutralize();
        $this->assertEquals('CCN(=O)=O', $mol->toSmiles());
    }

    public function testReionize()
    {
        $mol = Molecule::fromSmiles('[O-]c1cc(C(=O)O)ccc1');
        $mol->reionize();
        $this->assertEquals('O=C([O-])c1cccc(O)c1', $mol->toSmiles());
    }

    public function testCanonicalTautomer()
    {
        $mol = Molecule::fromSmiles('OC(O)C(=N)CO');
        $mol->canonicalTautomer();
        $this->assertEquals('NC(CO)C(=O)O', $mol->toSmiles());
    }

    public function testChargeParent()
    {
        $mol = Molecule::fromSmiles('[Pt]CCN(=O)=O', sanitize: false);
        $mol->chargeParent();
        $this->assertEquals('CC[N+](=O)[O-]', $mol->toSmiles());
    }

    public function testFragmentParent()
    {
        $mol = Molecule::fromSmiles('[Pt]CCN(=O)=O', sanitize: false);
        $mol->fragmentParent();
        $this->assertEquals('[CH2-]C[N+](=O)[O-]', $mol->toSmiles());
    }

    public function testToSmiles()
    {
        $mol = Molecule::fromSmiles('Cc1ccccc1');
        $this->assertEquals('Cc1ccccc1', $mol->toSmiles());
    }

    public function testToSmarts()
    {
        $mol = Molecule::fromSmiles('Cc1ccccc1');
        $this->assertEquals('[#6]-[#6]1:[#6]:[#6]:[#6]:[#6]:[#6]:1', $mol->toSmarts());
    }

    public function testToCXSmiles()
    {
        $mol = Molecule::fromSmiles('Cc1ccccc1');
        $this->assertEquals('Cc1ccccc1', $mol->toCXSmiles());
    }

    public function testToCXSmarts()
    {
        $mol = Molecule::fromSmiles('Cc1ccccc1');
        $this->assertEquals('[#6]-[#6]1:[#6]:[#6]:[#6]:[#6]:[#6]:1', $mol->toCXSmarts());
    }

    public function testToJson()
    {
        $mol = Molecule::fromSmiles('Cc1ccccc1');
        $data = json_decode($mol->toJson());
        $this->assertEquals(11, $data->rdkitjson->version);
    }

    public function testToSvg()
    {
        $mol = Molecule::fromSmiles('Cc1ccccc1');
        $svg = $mol->toSvg();
        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString("width='250px'", $svg);
        $this->assertStringContainsString("height='200px'", $svg);
    }

    public function testToSvgWidthHeight()
    {
        $mol = Molecule::fromSmiles('Cc1ccccc1');
        $svg = $mol->toSvg(width: 500, height: 400);
        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString("width='500px'", $svg);
        $this->assertStringContainsString("height='400px'", $svg);
    }

    public function testClone()
    {
        $mol = Molecule::fromSmiles('CCO');
        $mol2 = clone $mol;
        $mol->addHs();
        $this->assertEquals('[H]OC([H])([H])C([H])([H])[H]', $mol->toSmiles());
        $this->assertEquals('CCO', $mol2->toSmiles());
    }
}
