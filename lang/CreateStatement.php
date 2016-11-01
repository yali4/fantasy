<?php

class CreateStatement
{
    protected $statements;

    public function __construct($statements = array())
    {
        $this->statements = $statements;
    }

    public function addStatement($statement)
    {
        $this->statements[] = $statement;
    }

    public function getStatements()
    {
        return $this->statements;
    }

    public function count()
    {
        return count($this->statements);
    }

    public function isEmpty()
    {
        return $this->count() === 0;
    }

    public function first()
    {
        return array_shift($this->statements);
    }
}