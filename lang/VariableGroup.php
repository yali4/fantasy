<?php

class VariableGroup
{
    protected $variables = array();

    public function push(CreateVariable $variable)
    {
        array_push($this->variables, $variable);
    }

    public function pull()
    {
        return array_shift($this->variables);
    }

    public function count()
    {
        return count($this->variables);
    }
}