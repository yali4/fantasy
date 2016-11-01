<?php

function interpret_value($scope, $value)
{
    if ( $value instanceof VariableReference )
    {
        return resolve_variable($scope, $value->getName());
    }

    if ( $value instanceof CreateStatement )
    {
        return interpret_sst($scope, $value->getStatements());
    }

    if ( $value instanceof CallFunction )
    {
        return resolve_function($scope, $value->getName(), $value->getArgs());
    }

    return $value;
}

function interpret_sst($scope, $statements)
{
    $action = array();

    while ( count($statements) )
    {
        $statement = array_shift($statements);

        if ( $statement instanceof CreateStatement )
        {
            $action[] = interpret_sst($scope, $statement->getStatements());

            continue;
        }

        if ( $statement instanceof VariableReference )
        {
            $action[] = resolve_variable($scope, $statement->getName());

            continue;
        }

        if ( $statement instanceof LogicalOperator )
        {
            if ( count($action) === 0 )
            {
                throw new Unexpected_Operator();
            }

            if ( $statement->getOperator() === '||' )
            {
                $result = interpret_operators($action);

                if ( $result->isTrue() )
                {
                    return $result;
                }

                return interpret_sst($scope, $statements);
            }

            if ( $statement->getOperator() === '&&' )
            {
                $result = interpret_operators($action);

                if ( $result->isFalse() )
                {
                    return $result;
                }

                return interpret_sst($scope, $statements);
            }
        }

        if ( $statement instanceof Variable || $statement instanceof Operator )
        {
            array_push($action, $statement);
        }
    }

    return interpret_operators($action);
}

function interpret_operators($statements)
{
    while( $result = find_operator($statements, 'arithmetic') )
    {
        list($key, $value) = $result;

        $first = $statements[$key-1];
        $second = $statements[$key+1];

        $left = $first->value();
        $right = $second->value();

        $operator = $value->getOperator();

        if ( $operator === '+' )
        {
            if ( $first instanceof StringVariable || $second instanceof StringVariable )
            {
                $value = new StringVariable($left . $right);
            }
            else
            {
                $value = new IntegerVariable($left + $right);
            }
        }
        else if ( $operator === '-' )
        {
            $value = new IntegerVariable($left - $right);
        }
        else if ( $operator === '*' )
        {
            $value = new IntegerVariable($left * $right);
        }
        else if ( $operator === '/' )
        {
            $value = new IntegerVariable($left / $right);
        }
        else if ( $operator === '%' )
        {
            $value = new IntegerVariable($left % $right);
        }

        array_splice($statements, ($key-1), ($key+2), array($value));
    }

    while( $result = find_operator($statements, 'comparison') )
    {
        list($key, $value) = $result;

        $left = $statements[$key-1]->value();
        $right = $statements[$key+1]->value();

        $operator = $value->getOperator();

        if ( $operator === '==' )
        {
            $value = new BooleanVariable($left == $right);
        }
        else if ( $operator === '!=' )
        {
            $value = new BooleanVariable($left != $right);
        }
        else if ( $operator === '>' )
        {
            $value = new BooleanVariable($left > $right);
        }
        else if ( $operator === '<' )
        {
            $value = new BooleanVariable($left < $right);
        }

        array_splice($statements, ($key-1), ($key+2), array($value));
    }

    return $statements[0];
}

function find_operator($statements, $operators)
{
    foreach($statements as $key => $statement)
    {
        if ( $operators === 'arithmetic' && $statement instanceof ArithmeticOperator )
        {
            return array($key, $statement);
        }

        else if ( $operators === 'comparison' && $statement instanceof ComparisonOperator )
        {
            return array($key, $statement);
        }
    }

    return false;
}

function resolve_variable($scope, $name)
{
    global $variables;

    if ( isset($variables[$scope][$name]) )
    {
        return $variables[$scope][$name]->getValue();
    }

    $scope = explode('.', $scope);

    while( count($scope) )
    {
        array_pop($scope);

        $index = join('.', $scope);

        if ( isset($variables[$index][$name]) )
        {
            return $variables[$index][$name]->getValue();
        }
    }

    throw new Undefined_Variable();
}

function resolve_function($scope, $name, $args)
{
    global $functions;

    if ( isset($functions[$scope][$name]) )
    {
        return $functions[$scope][$name]->getResult($args);
    }

    $scope = explode('.', $scope);

    while( count($scope) )
    {
        array_pop($scope);

        $index = join('.', $scope);

        if ( isset($functions[$index][$name]) )
        {
            return $functions[$index][$name]->getResult($args);
        }
    }

    throw new Undefined_Function();
}

abstract class Variable
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function value()
    {
        return $this->value;
    }

    function getType()
    {
        if ( $this instanceof IntegerVariable )
        {
            return 'int';
        }
        if ( $this instanceof StringVariable )
        {
            return 'str';
        }
        if ( $this instanceof BooleanVariable )
        {
            return 'bool';
        }
        if ( $this instanceof ArrayVariable )
        {
            return 'arr';
        }
        if ( $this instanceof ObjectVariable )
        {
            return 'obj';
        }

        return null;
    }
}

class NullVariable extends Variable {}

class StringVariable extends Variable {}

class IntegerVariable extends Variable{}

class BooleanVariable extends Variable
{
    public function isTrue()
    {
        return $this->value === true;
    }

    public function isFalse()
    {
        return $this->value === false;
    }
}

class ArrayVariable extends Variable {}

class ObjectVariable extends Variable {}