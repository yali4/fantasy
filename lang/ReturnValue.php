<?php

class ReturnValue
{
    protected $value;

    protected $scope;

    public function __construct(/*$scope,*/ $value)
    {
        //$this->scope = $scope;

        $this->value = $value;
    }

    public function getResult($scope = null)
    {
        if ( $scope === null )
        {
            $scope = $this->scope;
        }

        return interpret_value($scope, $this->value);
    }
}