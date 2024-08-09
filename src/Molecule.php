<?php

namespace RDKit;

class Molecule
{
    private $ffi;
    private $ptr;
    private $sz;

    private function __construct()
    {
        $this->ffi = FFI::instance();
    }

    public function __clone()
    {
        $this->loadSmiles($this->toSmiles(), sanitize: false, kekulize: false, removeHs: false);
    }

    public static function fromSmiles($input, ...$options)
    {
        $mol = new self();
        $mol->loadSmiles($input, ...$options);
        return $mol;
    }

    public static function fromSmarts($input)
    {
        $mol = new self();
        $mol->loadSmarts($input);
        return $mol;
    }

    public function numAtoms($onlyExplicit = true)
    {
        if ($onlyExplicit) {
            return array_sum(array_map(fn ($v) => count($v->atoms), $this->jsonData()->molecules));
        } else {
            return (int) $this->descriptors()->NumAtoms;
        }
    }

    public function numHeavyAtoms()
    {
        return (int) $this->descriptors()->NumHeavyAtoms;
    }

    public function hasMatch($pattern, $useChirality = true)
    {
        return count($this->match($pattern, useChirality: $useChirality, maxMatches: 1)) > 0;
    }

    public function match($pattern, $useChirality = true, $maxMatches = null)
    {
        $this->checkPattern($pattern);

        $details = [
            'useChirality' => $useChirality
        ];
        if (!is_null($maxMatches)) {
            $details['maxMatches'] = $maxMatches;
        }
        $json = $this->checkString($this->ffi->get_substruct_matches($this->getPtr(), $this->sz->cdata, $pattern->getPtr(), $pattern->sz->cdata, $this->toDetails($details)));

        $matches = (array) json_decode($json);
        return array_map(fn ($v) => $v->atoms, $matches);
    }

    public function fragments($sanitize = true)
    {
        try {
            $szArr = $this->ffi->new('size_t*');
            $numFrags = $this->ffi->new('size_t');
            $details = [
                'sanitizeFrags' => $sanitize
            ];
            $arr = $this->ffi->get_mol_frags($this->getPtr(), $this->sz->cdata, \FFI::addr($szArr), \FFI::addr($numFrags), $this->toDetails($details), null);
            $this->checkPtr($arr);

            $fragments = [];
            for ($i = 0; $i < $numFrags->cdata; $i++) {
                $ptr = $arr[$i];
                $sz = $this->ffi->new('size_t');
                $sz->cdata = $szArr[$i];

                $mol = new self();
                $mol->loadPtr($ptr, $sz);
                $fragments[] = $mol;
            }
            return $fragments;
        } finally {
            $this->freePtr($szArr);
            $this->freePtr($arr);
        }
    }

    public function rdkitFingerprint($minPath = 1, $maxPath = 7, $length = 2048, $bitsPerHash = 2, $useHs = true, $branchedPaths = true, $useBondOrder = true)
    {
        $length = (int) $length;
        $this->checkLength($length);

        $details = [
            'minPath' => (int) $minPath,
            'maxPath' => (int) $maxPath,
            'nBits' => $length,
            'nBitsPerHash' => (int) $bitsPerHash,
            'useHs' => $useHs,
            'branchedPaths' => $branchedPaths,
            'useBondOrder' => $useBondOrder
        ];
        return $this-> checkString($this->ffi->get_rdkit_fp($this->getPtr(), $this->sz->cdata, $this->toDetails($details)));
    }

    public function morganFingerprint($radius = 3, $length = 2048, $useChirality = true, $useBondTypes = true)
    {
        $radius = (int) $radius;
        if ($radius < 1) {
            throw new \InvalidArgumentException('radius must be greater than 0');
        }

        $length = (int) $length;
        $this->checkLength($length);

        $details = [
            'radius' => $radius,
            'nBits' => $length,
            'useChirality' => $useChirality,
            'useBondTypes' => $useBondTypes
        ];
        return $this-> checkString($this->ffi->get_morgan_fp($this->getPtr(), $this->sz->cdata, $this->toDetails($details)));
    }

    public function patternFingerprint($length = 2048, $tautomeric = false)
    {
        $length = (int) $length;
        $this->checkLength($length);

        $details = [
            'nBits' => $length,
            'tautomericFingerprint' => $tautomeric
        ];
        return $this-> checkString($this->ffi->get_pattern_fp($this->getPtr(), $this->sz->cdata, $this->toDetails($details)));
    }

    public function topologicalTorsionFingerprint($length = 2048)
    {
        $length = (int) $length;
        $this->checkLength($length);

        $details = [
            'nBits' => $length,
        ];
        return $this->checkString($this->ffi->get_topological_torsion_fp($this->getPtr(), $this->sz->cdata, $this->toDetails($details)));
    }

    public function atomPairFingerprint($length = 2048, $minLength = 1, $maxLength = 30)
    {
        $length = (int) $length;
        $this->checkLength($length);

        $details = [
            'nBits' => $length,
            'minLength' => (int) $minLength,
            'maxLength' => (int) $maxLength,
        ];
        return $this->checkString($this->ffi->get_atom_pair_fp($this->getPtr(), $this->sz->cdata, $this->toDetails($details)));
    }

    public function maccsFingerprint()
    {
        // remove bit 0 for correct length
        // https://github.com/rdkit/rdkit/issues/1726
        return substr($this->checkString($this->ffi->get_maccs_fp($this->getPtr(), $this->sz->cdata)), 1);
    }

    public function addHs()
    {
        $this->checkStatus($this->ffi->add_hs(\FFI::addr($this->getPtr()), \FFI::addr($this->sz)));
    }

    public function removeHs()
    {
        $this->checkStatus($this->ffi->remove_all_hs(\FFI::addr($this->getPtr()), \FFI::addr($this->sz)));
    }

    public function cleanup()
    {
        $this->checkStatus($this->ffi->cleanup(\FFI::addr($this->getPtr()), \FFI::addr($this->sz), $this->toDetails()));
    }

    public function normalize()
    {
        $this->checkStatus($this->ffi->normalize(\FFI::addr($this->getPtr()), \FFI::addr($this->sz), $this->toDetails()));
    }

    public function neutralize()
    {
        $this->checkStatus($this->ffi->neutralize(\FFI::addr($this->getPtr()), \FFI::addr($this->sz), $this->toDetails()));
    }

    public function reionize()
    {
        $this->checkStatus($this->ffi->reionize(\FFI::addr($this->getPtr()), \FFI::addr($this->sz), $this->toDetails()));
    }

    public function canonicalTautomer()
    {
        $this->checkStatus($this->ffi->canonical_tautomer(\FFI::addr($this->getPtr()), \FFI::addr($this->sz), $this->toDetails()));
    }

    public function chargeParent()
    {
        $this->checkStatus($this->ffi->charge_parent(\FFI::addr($this->getPtr()), \FFI::addr($this->sz), $this->toDetails()));
    }

    public function fragmentParent()
    {
        $this->checkStatus($this->ffi->fragment_parent(\FFI::addr($this->getPtr()), \FFI::addr($this->sz), $this->toDetails()));
    }

    public function toSmiles()
    {
        return $this->checkString($this->ffi->get_smiles($this->getPtr(), $this->sz->cdata, $this->toDetails()));
    }

    public function toSmarts()
    {
        return $this->checkString($this->ffi->get_smarts($this->getPtr(), $this->sz->cdata, $this->toDetails()));
    }

    public function toCXSmiles()
    {
        return $this->checkString($this->ffi->get_cxsmiles($this->getPtr(), $this->sz->cdata, $this->toDetails()));
    }

    public function toCXSmarts()
    {
        return $this->checkString($this->ffi->get_cxsmarts($this->getPtr(), $this->sz->cdata, $this->toDetails()));
    }

    public function toJson()
    {
        return $this->checkString($this->ffi->get_json($this->getPtr(), $this->sz->cdata, $this->toDetails()));
    }

    public function toSvg($width = 250, $height = 200)
    {
        $details = [
            'width' => $width,
            'height' => $height
        ];
        return $this->checkString($this->ffi->get_svg($this->getPtr(), $this->sz->cdata, $this->toDetails($details)));
    }

    private function loadSmiles($input, $sanitize = true, $kekulize = true, $removeHs = true)
    {
        $sz = $this->ffi->new('size_t');
        $details = [
            'sanitize' => $sanitize,
            'kekulize' => $kekulize,
            'removeHs' => $removeHs
        ];
        $ptr = $this->ffi->get_mol($input, \FFI::addr($sz), $this->toDetails($details));
        $this->loadPtr($ptr, $sz);
    }

    private function loadSmarts($input)
    {
        $sz = $this->ffi->new('size_t');
        $ptr = $this->ffi->get_qmol($input, \FFI::addr($sz), $this->toDetails());
        $this->loadPtr($ptr, $sz);
    }

    private function loadPtr($ptr, $sz)
    {
        if (is_null($ptr)) {
            throw new \InvalidArgumentException('invalid input');
        }

        $this->ptr = new Pointer($ptr);
        $this->sz = $sz;
    }

    private function getPtr()
    {
        return $this->ptr->ptr;
    }

    private function toDetails($details = [])
    {
        return json_encode((object) $details);
    }

    private function jsonData()
    {
        return json_decode($this->toJson());
    }

    private function descriptors()
    {
        return json_decode($this->checkString($this->ffi->get_descriptors($this->getPtr(), $this->sz->cdata)));
    }

    private function checkPattern($pattern)
    {
        if (!($pattern instanceof self)) {
            throw new \InvalidArgumentException('expected ' . self::class);
        }
    }

    private function checkLength($length)
    {
        if ($length < 1) {
            throw new \InvalidArgumentException('length must be greater than 0');
        }
    }

    private function checkPtr($ptr)
    {
        if (is_null($ptr) || \FFI::isNull($ptr)) {
            throw new \Exception('bad pointer');
        }
    }

    private function freePtr($ptr)
    {
        if (!is_null($ptr) && !\FFI::isNull($ptr)) {
            $this->ffi->free_ptr($ptr);
        }
    }

    private function checkString($ptr)
    {
        $this->checkPtr($ptr);
        try {
            return \FFI::string($ptr);
        } finally {
            $this->freePtr($ptr);
        }
    }

    private function checkStatus($status)
    {
        if ($status != 1) {
            throw new \Exception('bad status');
        }
    }
}
