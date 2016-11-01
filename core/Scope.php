<?php

class Scope
{
    protected $scopes = array();

    public function __construct() {}

    public function push(ScopeItem $scope)
    {
        $this->scopes[] = $scope;
    }

    public function back()
    {
        array_pop($this->scopes);
    }

    public function get()
    {
        $result = array();

        foreach($this->scopes as $scope)
        {
            if ( $scope->getScope() !== null )
            {
                $result[] = $scope->getScope();
            }
        }

        return join('.', $result);
    }

    public function current()
    {
        return end($this->scopes);
    }
}

class ScopeItem
{
    protected $alias;

    protected $scope;

    public function __construct($alias, $scope = null)
    {
        $this->alias = $alias;

        $this->scope = $scope;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function getScope()
    {
        return $this->scope;
    }
}