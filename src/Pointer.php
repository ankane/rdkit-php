<?php

namespace RDKit;

class Pointer
{
    public $ptr;

    public function __construct($ptr)
    {
        $this->ptr = $ptr;
    }

    public function __destruct()
    {
        FFI::instance()->free_ptr($this->ptr);
    }
}
