<?php

class Functions extends Variables
{
    public function getFunction($name)
    {
        $action = new CreateFunction($name);

        $this->match('(', false);

        $action->setArguments($this->getFunctionArguments());

        $this->match(')', false);

        $code = $this->source;

        $code->skipWhiteSpace();

        if ( $code->char() == '=' && $code->nextChar() == '>' )
        {
            $code->move(2);

            $action->setReturnType($this->statement(GET_FUNCTION_RETURN_TYPE));
        }

        //$action->setScope($scope->get());

        //$scope->push(new ScopeItem('function', $name));

        $this->match('{');

        $action->addStack($this->start(GET_FUNCTION_STACK));

        //$interpreter = new Interpreter($this->source/*, $scope*/);

        //$stack = $interpreter->start(array('function_stack'));

        //$action->addStack($stack);

        $this->match('}');

        //var_dump($this->source->source(100)); exit;

        //var_dump($this->source->source(100)); exit;

        //$scope->back();

        return $action;
    }

    public function getFunctionArguments()
    {
        $result = array();

        $code = $this->source;

        while ( $code->loop() )
        {
            if ( $code->isWhiteSpace() )
            {
                $code->next();

                continue;
            }

            if ( $code->isDelimiter() )
            {
                $code->next();

                continue;
            }

            $argument = $this->statement(GET_FUNCTION_ARGUMENT);

            if ( $argument instanceof ArgumentVariable )
            {
                $result[] = $argument;

                continue;
            }

            break;
        }

        return $result;
    }

    public function getFunctionArgument($type, $name)
    {
        $value = new ArgumentVariable($type, $name);

        if ( $this->matchIs('=') )
        {
            $value->setValue($this->statement(GET_FUNCTION_ARGUMENT_VALUE));
        }

        return $value;
    }

    public function getFunctionParameters()
    {
        $result = array();

        $code = $this->source;

        while ( $code->loop() )
        {
            if ( $code->isWhiteSpace() )
            {
                $code->next();

                continue;
            }

            if ( $code->isDelimiter() )
            {
                $code->next();

                continue;
            }

            if ( $code->isConditionEnd() )
            {
                $code->next();

                break;
            }

            $result[] = $this->statement(GET_FUNCTION_PARAMETER);
        }

        return $result;
    }

    public function getReturnValue()
    {
        $action = new ReturnValue($this->statement(GET_FUNCTION_RETURN));

        $this->match(';');

        return $action;
    }

    public function getIfCondition()
    {
        $action = new CreateCondition();

        $this->match('(');

        //var_dump($this->source->source(100)); //exit;

        //var_dump(); exit;

        $action->addStatement($this->statement(GET_IF_CONDITION));

        $this->match(')');

        $this->match('{');

        //$interpreter = new Interpreter($this->source/*, $scope*/);

        //$stack = $interpreter->start(array('if_condition_stack'));

        $action->addStack($this->start(GET_IF_CONDITION_STACK));

        $this->match('}');

        //$scope->push(new ScopeItem('condition'));

        //$interpreter = new Interpreter($code, $scope);

        //$stack = $interpreter->start($options);

        //$action->setScope($scope->get());

        //$action->addStack($stack);

        //$scope->back();

        return $action;
    }
}