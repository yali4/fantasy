<?php

class Interpreter extends Functions
{
    protected $source;

    //protected $scope;

    protected $reservedPattern = '/^true|false|null|if|else|elseif|import|return/';

    public function __construct(Source $source/*, Scope $scope*/)
    {
        $this->source = $source;

        //$this->scope = $scope;
    }

    public function start($options)
    {
        $stack = new Stack();

        while( $result = $this->statement($options) )
        {
            if ( $result instanceof VariableGroup )
            {
                while ( $variable = $result->pull() )
                {
                    $stack->add($variable);
                }

                continue;
            }

            $stack->add($result);
        }

        return $stack;
    }

    public function matchIs($token, $skipEndOfLine = true, $skipWhiteSpace = true)
    {
        while( $this->source->loop() )
        {
            if ( $skipWhiteSpace && $this->source->isWhiteSpaceChar() )
            {
                $this->source->next();

                continue;
            }

            if ( $skipEndOfLine && $this->source->isEndOfLineChar() )
            {
                $this->source->next();

                continue;
            }

            if ( $this->source->char() === $token )
            {
                $this->source->next();

                return true;
            }

            break;
        }

        return false;
    }

    public function match($token, $skipEndOfLine = true, $skipWhiteSpace = true)
    {
        while( $this->source->loop() )
        {
            if ( $skipWhiteSpace && $this->source->isWhiteSpaceChar() )
            {
                $this->source->next();

                continue;
            }

            if ( $skipEndOfLine && $this->source->isEndOfLineChar() )
            {
                $this->source->next();

                continue;
            }

            if ( $this->source->char() === $token )
            {
                $this->source->next();

                return true;
            }

            break;
        }

        //return false;

        var_dump("Beklenen Anahtar: ". $token);

        var_dump($this->source->source(100));

        throw new Unexpected_Token();
    }

    public function statement($search = null)
    {
        global $callCount;

        $callCount++;

        $result = new CreateStatement();

        $code = $this->source;

        while ( $code->loop() )
        {

            // Skip White Spaces and Tab for Stacks
            if ( $search == GET_GLOBAL_STACK || $search == GET_FUNCTION_STACK ||
                $search == GET_IF_CONDITION || $search == GET_IF_CONDITION_STACK ||
                $search == GET_STATEMENT )
            {
                if (!$code->skipWhiteSpaceAndEndOfLine())
                {
                    break;
                }
            }
            // Skip Only White Spaces
            else
            {
                if (!$code->skipWhiteSpace())
                {
                    break;
                }
            }

            // Tek Satır Yorumlar
            if ( $code->char() === '/' && $code->nextChar() === '/' )
            {
                //var_dump($this->source->source(100)); exit;

                $code->move(2);

                while ( $code->loop() )
                {
                    if ( $code->isEndOfLine() )
                    {
                        break;
                    }

                    $code->next();
                }

                continue;
            }

            // Çok Satır Yorumlar
            if ( $code->char() === '/' && $code->nextChar() === '*' )
            {
                $code->move(2);

                while( $code->loop() )
                {
                    if ( $code->char() === '*' && $code->nextChar() === '/' )
                    {
                        $code->move(2);

                        break;
                    }

                    $code->next();
                }

                continue;
            }

            // Character Alias
            $char = $code->char();

            // If Not Variable Name
            if ( $search != GET_VARIABLE_NAME && $search != GET_FUNCTION_NAME && $search != GET_FUNCTION_RETURN_TYPE )
            {

                // String
                if ( is_start_string($char) )
                {
                    $code->next();

                    $value = $this->getString($char);

                    if ( $search == GET_OBJECT_KEY ) return $value;

                    $result->addStatement(new StringVariable($value));

                    continue;
                }

                // Exception
                if ( $search == GET_OBJECT_KEY )
                {
                    //var_dump($options, $code->source(100)); exit;

                    throw new Unexpected_Token();
                }

                // Number
                if ( ($char >= '0' && $char <= '9') || $char == '+' || $char == '-' )
                {
                    $code->next();

                    $integer = $char;

                    while ( $code->loop() )
                    {
                        $char = $code->char();

                        if ( ($char >= '0' && $char <= '9') || $char == '.' )
                        {
                            $code->next();

                            $integer .= $char;

                            continue;
                        }

                        break;
                    }

                    $result->addStatement(new IntegerVariable($integer));

                    continue;
                }

                // Array
                if ( is_start_array($char) )
                {
                    $code->next();

                    $value = $this->getArray();

                    $result->addStatement(new ArrayVariable($value));

                    continue;
                }

                // Object
                if ( is_start_object($char) )
                {
                    $code->next();

                    $value = $this->getObject();

                    $result->addStatement(new ObjectVariable($value));

                    continue;
                }
            }

            // controller
            if ( $char == '_'  || ( $char >= 'a' && $char <= 'z' ) || ($char >= 'A' && $char <= 'Z') )
            {
                $string = $char;

                $code->next();

                // Get String Argument
                while ( $code->loop() )
                {
                    $char = $code->char();

                    if ( $char == '_' || ($char >= '0' && $char <= '9') ||
                        ( $char >= 'a' && $char <= 'z' )  || ($char >= 'A' && $char <= 'Z') )
                    {
                        $code->next();

                        $string .= $char;

                        continue;
                    }

                    break;
                }

                // Clean White Space
                $code->skipWhiteSpace();

                // Variable or Function Name Getter
                if ( $search == GET_VARIABLE_NAME || $search == GET_FUNCTION_NAME || $search == GET_FUNCTION_ARGUMENT_NAME )
                {
                    return $string;
                }

                // Function Define?
                if ( $string === 'func' )
                {
                    $name = $this->statement(GET_FUNCTION_NAME);

                    return $this->getFunction($name);
                }

                // Variable Define or Function Argument?
                if ( $string === 'int' || $string === 'arr' || $string === 'obj' ||
                    $string === 'var' || $string === 'str' || $string === 'bool' )
                {
                    // Argument
                    if ( $search == GET_FUNCTION_ARGUMENT )
                    {
                        return $this->getFunctionArgument($string, $this->statement(GET_FUNCTION_ARGUMENT_NAME));
                    }

                    // Return Type
                    if ( $search == GET_FUNCTION_RETURN_TYPE )
                    {
                        return $string;
                    }

                    return $this->getVariables($string);
                }

                // Boolean Control
                if ( $string === 'true' || $string === 'false' )
                {
                    $result->addStatement(new BooleanVariable($string));

                    continue;
                }
                // Null Control
                else if ( $string === 'null' )
                {
                    $result->addStatement(new NullVariable($string));

                    continue;
                }

                // If Condition
                if ( $string === 'if' )
                {
                    return $this->getIfCondition();
                }

                // Return Value
                if ( $string === 'return' )
                {
                    return $this->getReturnValue($search);
                }

                // Function Calling
                if ( $code->char() == '(' )
                {
                    $code->next();

                    $action = new CallFunction($string, $this->getFunctionParameters());

                    $result->addStatement($action);

                    continue;
                }

                // If Function Argument
                if ( $search == GET_FUNCTION_ARGUMENT )
                {
                    return $this->getFunctionArgument('var', $string);
                }

                // If Not Function Argument
                else
                {
                    // Assign Value
                    if ( $code->char() == '=' && $code->nextChar() != '=' )
                    {
                        $code->next();

                        $action = new AssignValue($string, $this->statement());

                        $result->addStatement($action);

                        $this->match(';');

                        continue;
                    }
                }

                // If Variable Reference
                if ( $search != GET_FUNCTION_ARGUMENT && $search != GET_FUNCTION_ARGUMENT_VALUE )
                {
                    // Variable Reference
                    $value = new VariableReference($string);

                    $result->addStatement($value);

                    continue;
                }

                continue;

                //break;
            }


            //
            if ( $search != GET_FUNCTION_ARGUMENT && $search != GET_FUNCTION_ARGUMENT_VALUE )
            {

                // Logical Operators
                //and|or|xor|\!|
                if ( preg_match('/^(&&|\|\|)\s*/', $code->source(5), $matches) )
                {
                    $code->move(strlen($matches[0]));

                    $action = new LogicalOperator($matches[1]);

                    $result->addStatement($action);

                    continue;
                }

                // Comparison Operators
                if ( preg_match('/^(<=>|===|!==|==|!=|<>|>=|<=|>|<)\s*/', $code->source(5), $matches) )
                {
                    $code->move(strlen($matches[0]));

                    $action = new ComparisonOperator($matches[1]);

                    $result->addStatement($action);

                    continue;
                }

                // Arithmetic Operators
                if (

                ($code->char() == '+' && $code->nextChar() != '+')
                    ||
                ($code->char() == '/' && $code->nextChar() != '/')
                    ||
                ($code->char() == '*' && $code->nextChar() != '*')
                    ||
                ($code->char() == '-' && $code->nextChar() != '-')
                    ||
                ($code->char() == '%' && $code->nextChar() != '%')

                //preg_match('/^(\*|\/|\+|\-|\%)\s*/', $code->source(5), $matches)

                )
                {
                    //var_dump($matches);

                    //$code->move(strlen($matches[0]));

                    $code->next();

                    $action = new ArithmeticOperator( $char /*$matches[1]*/);

                    $result->addStatement($action);

                    continue;
                }


                // Condition Begin
                if ( $code->isConditionBegin() )
                {
                    $code->next();

                    $action = $this->statement(GET_STATEMENT);

                    $result->addStatement($action);

                    continue;
                }

                // Condition End for Statement Caller
                if ( $code->isConditionEnd() && $search == GET_STATEMENT )
                {
                    $code->next();

                    break;
                }

            }

            break;

            /*
            var_dump($result);

            var_dump($options);

            var_dump(__FILE__, __LINE__, $code->source()); exit;

            throw new Unexpected_Token();
            */
        }

        $count = $result->count();

        if ( $count === 0 )
        {
            return false;
        }
        else if ( $count === 1 )
        {
            return $result->first();
        }

        return $result;
    }





}