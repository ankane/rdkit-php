<?php

use Tests\TestCase;

final class MoleculeTest extends TestCase
{
    public function testFromSmiles()
    {
        $mol = RDKit\Molecule::fromSmarts('CCO');
        $this->assertInstanceOf(RDKit\Molecule::class, $mol);
    }

    public function testFromSmilesInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('invalid input');

        RDKit\Molecule::fromSmiles('?');
    }

    public function testFromSmarts()
    {
        $mol = RDKit\Molecule::fromSmarts('ccO');
        $this->assertInstanceOf(RDKit\Molecule::class, $mol);
    }

    public function testFromSmartsInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('invalid input');

        RDKit\Molecule::fromSmarts('?');
    }

    public function testNumAtoms()
    {
        $mol = RDKit\Molecule::fromSmiles('CCO');
        $this->assertEquals(3, $mol->numAtoms());

        $mol = RDKit\Molecule::fromSmiles('[H]OC([H])([H])C([H])([H])[H]', removeHs: false);
        $this->assertEquals(9, $mol->numAtoms());
    }

    public function testNumHeavyAtoms()
    {
        $mol = RDKit\Molecule::fromSmiles('CCO');
        $this->assertEquals(3, $mol->numHeavyAtoms());
    }

    public function testMatch()
    {
        $mol = RDKit\Molecule::fromSmiles('c1ccccc1O');
        $pattern = RDKit\Molecule::fromSmarts('ccO');
        $this->assertTrue($mol->hasMatch($pattern));
        $this->assertEquals([[0, 5, 6], [4, 5, 6]], $mol->match($pattern));
    }

    public function testMatchNone()
    {
        $mol = RDKit\Molecule::fromSmiles('c1ccccc1O');
        $pattern = RDKit\Molecule::fromSmarts('ccOc');
        $this->assertFalse($mol->hasMatch($pattern));
        $this->assertEmpty($mol->match($pattern));
    }

    public function testMatchFromSmiles()
    {
        $mol = RDKit\Molecule::fromSmiles('C1=CC=CC=C1OC');
        $this->assertTrue($mol->hasMatch(RDKit\Molecule::fromSmiles('COC')));
        $this->assertFalse($mol->hasMatch(RDKit\Molecule::fromSmarts('COC')));
        $this->assertTrue($mol->hasMatch(RDKit\Molecule::fromSmarts('COc')));
    }

    public function testMatchUseChirality()
    {
        $mol = RDKit\Molecule::fromSmiles('CC[C@H](F)Cl');
        $this->assertFalse($mol->hasMatch(RDKit\Molecule::fromSmiles('C[C@@H](F)Cl'), useChirality: true));
    }

    public function testMatchInvalidPattern()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected RDKit\Molecule');

        $mol = RDKit\Molecule::fromSmiles('c1ccccc1O');
        $mol->match('COC');
    }

    public function testFragments()
    {
        $mol = RDKit\Molecule::fromSmiles('n1ccccc1.CC(C)C.OCCCN');
        $expected = ['c1ccncc1', 'CC(C)C', 'NCCCO'];
        $this->assertEquals($expected, array_map(fn ($v) => $v->toSmiles(), $mol->fragments()));
    }

    public function testFragmentsSanitize()
    {
        $mol = RDKit\Molecule::fromSmiles('N(C)(C)(C)C.c1ccc1', sanitize: false);
        $expected = ['CN(C)(C)C', 'c1ccc1'];
        $this->assertEquals($expected, array_map(fn ($v) => $v->toSmiles(), $mol->fragments(sanitize: false)));
    }

    public function testRdkitFingerprint()
    {
        $mol = RDKit\Molecule::fromSmiles('Cc1ccccc1');
        $this->assertEquals(2048, strlen($mol->rdkitFingerprint()));
    }

    public function testMorganFingerprint()
    {
        $mol = RDKit\Molecule::fromSmiles('Cc1ccccc1');
        $this->assertEquals(2048, strlen($mol->morganFingerprint()));
    }

    public function testMorganFingerprintLength()
    {
        $mol = RDKit\Molecule::fromSmiles('Cc1ccccc1');
        $this->assertEquals(1024, strlen($mol->morganFingerprint(length: 1024)));
    }

    public function testMorganFingerprintRadiusZero()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('radius must be greater than 0');

        $mol = RDKit\Molecule::fromSmiles('Cc1ccccc1');
        $mol->morganFingerprint(radius: 0);
    }

    public function testPatternFingerprint()
    {
        $mol = RDKit\Molecule::fromSmiles('Cc1ccccc1');
        $this->assertEquals(2048, strlen($mol->patternFingerprint()));
    }

    public function testTopologicalTorsionFingerprint()
    {
        $mol = RDKit\Molecule::fromSmiles('Cc1ccccc1');
        $this->assertEquals(2048, strlen($mol->TopologicalTorsionFingerprint()));
    }

    public function testAtomPairFingerprint()
    {
        $mol = RDKit\Molecule::fromSmiles('Cc1ccccc1');
        $this->assertEquals(2048, strlen($mol->atomPairFingerprint()));
    }

    public function testMaccsFingerprint()
    {
        $mol = RDKit\Molecule::fromSmiles('Cc1ccccc1');
        $this->assertEquals(166, strlen($mol->maccsFingerprint()));
    }

    public function testAddHs()
    {
        $mol = RDKit\Molecule::fromSmiles('CCO');
        $mol->addHs();
        $this->assertEquals('[H]OC([H])([H])C([H])([H])[H]', $mol->toSmiles());
    }

    public function testRemoveHs()
    {
        $mol = RDKit\Molecule::fromSmiles('[H]OC([H])([H])C([H])([H])[H]', removeHs: false);
        $mol->removeHs();
        $this->assertEquals('CCO', $mol->toSmiles());
    }

    public function testCleanup()
    {
        $mol = RDKit\Molecule::fromSmiles('[Pt]CCN(=O)=O', sanitize: false);
        $mol->cleanup();
        $this->assertEquals('[CH2-]C[N+](=O)[O-].[Pt+]', $mol->toSmiles());
    }

    public function testNormalize()
    {
        $mol = RDKit\Molecule::fromSmiles('[CH2-]CN(=O)=O', sanitize: false);
        $mol->normalize();
        $this->assertEquals('[CH2-]C[N+](=O)[O-]', $mol->toSmiles());
    }

    public function testNeutralize()
    {
        $mol = RDKit\Molecule::fromSmiles('[CH2-]CN(=O)=O', sanitize: false);
        $mol->neutralize();
        $this->assertEquals('CCN(=O)=O', $mol->toSmiles());
    }

    public function testReionize()
    {
        $mol = RDKit\Molecule::fromSmiles('[O-]c1cc(C(=O)O)ccc1');
        $mol->reionize();
        $this->assertEquals('O=C([O-])c1cccc(O)c1', $mol->toSmiles());
    }

    public function testCanonicalTautomer()
    {
        $mol = RDKit\Molecule::fromSmiles('OC(O)C(=N)CO');
        $mol->canonicalTautomer();
        $this->assertEquals('NC(CO)C(=O)O', $mol->toSmiles());
    }

    public function testChargeParent()
    {
        $mol = RDKit\Molecule::fromSmiles('[Pt]CCN(=O)=O', sanitize: false);
        $mol->chargeParent();
        $this->assertEquals('CC[N+](=O)[O-]', $mol->toSmiles());
    }

    public function testFragmentParent()
    {
        $mol = RDKit\Molecule::fromSmiles('[Pt]CCN(=O)=O', sanitize: false);
        $mol->fragmentParent();
        $this->assertEquals('[CH2-]C[N+](=O)[O-]', $mol->toSmiles());
    }

    public function testToSmiles()
    {
        $mol = RDKit\Molecule::fromSmiles('Cc1ccccc1');
        $this->assertEquals('Cc1ccccc1', $mol->toSmiles());
    }

    public function testToSmarts()
    {
        $mol = RDKit\Molecule::fromSmiles('Cc1ccccc1');
        $this->assertEquals('[#6]-[#6]1:[#6]:[#6]:[#6]:[#6]:[#6]:1', $mol->toSmarts());
    }

    public function testToCXSmiles()
    {
        $mol = RDKit\Molecule::fromSmiles('Cc1ccccc1');
        $this->assertEquals('Cc1ccccc1', $mol->toCXSmiles());
    }

    public function testToCXSmarts()
    {
        $mol = RDKit\Molecule::fromSmiles('Cc1ccccc1');
        $this->assertEquals('[#6]-[#6]1:[#6]:[#6]:[#6]:[#6]:[#6]:1', $mol->toCXSmarts());
    }

    public function testToJson()
    {
        $mol = RDKit\Molecule::fromSmiles('Cc1ccccc1');
        $data = json_decode($mol->toJson());
        $this->assertEquals(11, $data->rdkitjson->version);
    }

    public function testToSvg()
    {
        $mol = RDKit\Molecule::fromSmiles('Cc1ccccc1');
        $svg = $mol->toSvg();
        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString("width='250px'", $svg);
        $this->assertStringContainsString("height='200px'", $svg);
    }

    public function testToSvgWidthHeight()
    {
        $mol = RDKit\Molecule::fromSmiles('Cc1ccccc1');
        $svg = $mol->toSvg(width: 500, height: 400);
        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString("width='500px'", $svg);
        $this->assertStringContainsString("height='400px'", $svg);
    }

    public function testClone()
    {
        $mol = RDKit\Molecule::fromSmiles('CCO');
        $mol2 = clone $mol;
        $mol->addHs();
        $this->assertEquals('[H]OC([H])([H])C([H])([H])[H]', $mol->toSmiles());
        $this->assertEquals('CCO', $mol2->toSmiles());
    }
}
