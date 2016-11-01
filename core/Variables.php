<?php

class Variables
{
    public function getVariables($type)
    {
        $group = new VariableGroup();

        $group->push($this->getVariable($type));

        while ( $this->matchIs(',') )
        {
            $group->push($this->getVariable($type));
        }

        $this->match(';', false);

        return $group;

        //$action->setValue($value/*, $scope->current()->getAlias()*/);

        //$action->setScope($scope->get());

        //var_dump($this->source->source(100)); exit;
    }

    public function getVariable($type)
    {
        $variable = new CreateVariable($type, $this->statement(GET_VARIABLE_NAME));

        if ( $this->matchIs('=', false) )
        {
            $variable->setValue($this->statement(GET_VARIABLE_VALUE));
        }

        return $variable;
    }

    public function getString($startWith)
    {
        $result = '';

        $code = $this->source;

        while( $code->loop() )
        {
            $char = $code->char();

            if ( $char === $startWith )
            {
                $code->next();

                break;
            }

            if ( $char === '\\' )
            {
                $nextChar = $code->nextChar();

                if ( $nextChar === $startWith )
                {
                    $code->move(2);

                    $result .= $nextChar;

                    continue;
                }
            }

            $code->next();

            $result .= $char;
        }

        return $result;
    }

    public function getArray()
    {
        $result = array();

        $lastChar = null;

        $code = $this->source;

        while ( $code->loop() )
        {
            if ( $code->isWhiteSpace() )
            {
                $code->next();

                continue;
            }

            $char = $code->char();

            if ( is_end_array($char) )
            {
                if ( is_delimiter($lastChar) )
                {
                    throw new Unexpected_Token();
                }

                $code->next();

                break;
            }

            if ( is_delimiter($char) )
            {
                if ( is_delimiter($lastChar) || count($result) === 0 )
                {
                    throw new Unexpected_Token();
                }

                $code->next();

                $lastChar = $char;

                continue;
            }

            if ( is_delimiter($lastChar) || count($result) === 0 )
            {
                $lastChar = null;

                $result[] = $this->statement(GET_ARRAY_VALUE);

                continue;
            }

            var_dump($code->source(100));

            throw new Unexpected_Token();
        }

        return $result;
    }

    public function getObject()
    {
        $result = array();

        $lastChar = null;

        $currentKey = null;

        $code = $this->source;

        while ( $code->loop() )
        {
            if ( $code->isWhiteSpace() )
            {
                $code->next();

                continue;
            }

            $char = $code->char();

            if ( is_end_object($char) )
            {
                if ( is_delimiter($lastChar) || is_key_delimiter($lastChar) || $currentKey !== null )
                {
                    throw new Unexpected_Token();
                }

                $code->next();

                break;
            }

            if ( is_delimiter($char) )
            {
                if ( is_delimiter($lastChar) || is_key_delimiter($lastChar) || count($result) === 0 )
                {
                    throw new Unexpected_Token();
                }

                $code->next();

                $lastChar = $char;

                continue;
            }

            if ( is_key_delimiter($char) )
            {
                if ( is_key_delimiter($lastChar) || is_delimiter($lastChar) || $currentKey === null )
                {
                    throw new Unexpected_Token();
                }

                $code->next();

                $lastChar = $char;

                continue;
            }

            if ( $currentKey === null &&  (is_delimiter($lastChar) || count($result) === 0) )
            {
                $lastChar = null;

                $currentKey = $this->statement(GET_OBJECT_KEY);

                //var_dump($currentKey);

                continue;
            }

            if ( $currentKey !== null && is_key_delimiter($lastChar) )
            {
                $lastChar = null;

                $result[$currentKey] = $this->statement(GET_OBJECT_VALUE);

                //var_dump($result[$currentKey]);

                $currentKey = null;

                continue;
            }

            var_dump($code->source(100));

            throw new Unexpected_Token();
        }

        return $result;
    }
}