<?php

abstract class Operator
{
    protected $operator;

    public function __construct($operator)
    {
        $this->operator = $operator;
    }

    public function getOperator()
    {
        return $this->operator;
    }
}

class LogicalOperator extends Operator {}

class ComparisonOperator extends Operator {}

class ArithmeticOperator extends Operator {}