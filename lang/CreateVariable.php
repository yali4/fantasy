<?php

class CreateVariable
{
    protected $type;

    protected $name;

    protected $scope;

    protected $value;

    protected $statement;

    public function __construct($type, $name)
    {
        $this->type = $type;

        $this->name = $name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setScope($scope)
    {
        global $variables;

        $this->scope = $scope;

        $variables[$this->scope][$this->name] = $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getStatement()
    {
        return $this->statement;
    }

    public function setValue($value, $alias = null)
    {
        $this->value = $value;


        /*
        if ( $value instanceof Variable )
        {
            $this->value = $value;
        }
        else if ( $value instanceof CreateStatement || $value instanceof VariableReference || $value instanceof CallFunction )
        {
            if ( $alias === 'global' )
            {
                $this->value = interpret_value($this->scope, $value);
            }
            else
            {
                $this->statement = $value;
            }
        }
        else
        {
            $this->value = new NullVariable($value);
        }
        */
    }
}