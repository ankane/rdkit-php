<?php

namespace RDKit;

class Reaction
{
    private $ffi;
    private $ptr;
    private $sz;

    private function __construct()
    {
        $this->ffi = FFI::instance();
    }

    public static function fromSmarts($input)
    {
        $rxn = new self();
        $rxn->loadRxn($input);
        return $rxn;
    }

    public function toSvg($width = 250, $height = 200)
    {
        $details = [
            'width' => $width,
            'height' => $height
        ];
        return $this->checkString($this->ffi->get_rxn_svg($this->getPtr(), $this->sz->cdata, $this->toDetails($details)));
    }

    private function loadRxn($input)
    {
        $sz = $this->ffi->new('size_t');
        $ptr = $this->ffi->get_rxn($input, \FFI::addr($sz), $this->toDetails());
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
}
