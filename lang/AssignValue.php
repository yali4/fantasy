<?php

class AssignValue
{
    protected $name;

    protected $value;

    public function __construct($name, $value)
    {
        $this->name = $name;

        $this->value = $value;
    }
}