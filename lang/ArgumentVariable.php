<?php

class ArgumentVariable
{
    protected $type;

    protected $name;

    protected $value;

    public function __construct($type, $name)
    {
        $this->type = $type;

        $this->name = $name;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }
}