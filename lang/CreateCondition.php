<?php

class CreateCondition
{
    protected $stack;

    protected $scope;

    protected $statement;

    public function __construct() {}

    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    public function addStatement(CreateStatement $statement)
    {
        $this->statement = $statement;
    }

    public function addStack(Stack $stack)
    {
        $this->stack = $stack;
    }

    public function getStack()
    {
        return $this->stack;
    }

    public function getResult($scope = null)
    {
        if ( $scope === null )
        {
            $scope = $this->scope;
        }

        return interpret_value($scope, $this->statement);
    }
}