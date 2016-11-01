<?php

class SetObjectProperty
{
    protected $property;

    protected $statement;

    public function __construct($property, $statement)
    {
        $this->property = $property;

        $this->statement = $statement;
    }
}