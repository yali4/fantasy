<?php

class CallFunction
{
    protected $name;

    protected $args;

    public function __construct($name, $args)
    {
        $this->name = $name;

        $this->args = $args;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getArgs()
    {
        return $this->args;
    }
}