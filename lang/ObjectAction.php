<?php

class ObjectAction
{
    protected $name;

    protected $action;

    public function __construct($name, $action)
    {
        $this->name = $name;

        $this->action = $action;
    }
}