<?php

class StackPointer
{
    protected $pointer;

    public function __construct($pointer)
    {
        $this->set($pointer);
    }

    public function set($pointer)
    {
        $this->pointer = $pointer;
    }

    public function get()
    {
        return $this->pointer;
    }

    public function is($pointer)
    {
        return $this->pointer === $pointer;
    }
}

// Global or Function Scope
if ( $this->pointer->is(GLOBAL_SCOPE) || $this->pointer->is(FUNCTION_SCOPE) )
{
    // Function Scope
    if ( $this->pointer->is(FUNCTION_SCOPE) )
    {

    }
}