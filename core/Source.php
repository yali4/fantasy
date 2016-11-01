<?php

class Source
{
    protected $source;

    protected $pointer = 0;

    public function __construct($source)
    {
        $this->source = $source;
    }

    public function loop()
    {
        //return true;

        return isset($this->source[$this->pointer]);
    }

    public function next($pointer = 1)
    {
        $this->pointer += $pointer;

        /*
        if ( !isset($this->source[$this->pointer]) )
        {
            return false;
        }
        */

        return true;
    }

    public function move($pointer)
    {
        return $this->next($pointer);
    }

    public function char()
    {
        return $this->source[$this->pointer];
    }

    public function nextChar($pointer = 1)
    {
        $target = $this->pointer + $pointer;

        if ( isset($this->source[$target]) )
        {
            return $this->source[$target];
        }

        return null;
    }

    public function source($length = 10)
    {
        return substr($this->source, $this->pointer, $length);
    }

    public function isWhiteSpaceChar()
    {
        return ($this->char() == " " || $this->char() == "\t");
    }

    public function isEndOfLineChar()
    {
        return ($this->char() == "\n" || $this->char() == "\r");
    }

    public function skipWhiteSpace()
    {
        while( $this->loop() )
        {
            if ( $this->isWhiteSpaceChar() )
            {
                $this->next();

                continue;
            }

            return true;
        }

        return false;
    }

    public function skipEndOfLine()
    {
        while( $this->loop() )
        {
            if ( $this->isEndOfLineChar() )
            {
                $this->next();

                continue;
            }

            return true;
        }

        return false;
    }

    public function skipWhiteSpaceAndEndOfLine()
    {
        while( $this->loop() )
        {
            if ( $this->isWhiteSpaceChar() || $this->isEndOfLineChar() )
            {
                $this->next();

                continue;
            }

            return true;
        }

        return false;
    }

    public function isWhiteSpace()
    {
        return ( $this->char() == " " || $this->char() == "\n" || $this->char() == "\r" || $this->char() == "\t" );

        //return preg_match('/\s|\r?\n|\t/', $this->char());
    }

    public function isEndOfLine()
    {
        return preg_match('/\r?\n/', $this->char());
    }

    public function isArrayBegin()
    {
        return $this->char() === '[';
    }

    public function isArrayEnd()
    {
        return $this->char() === ']';
    }

    public function isScopeBegin()
    {
        return $this->char() === '{';
    }

    public function isScopeEnd()
    {
        return $this->char() === '}';
    }

    public function isConditionBegin()
    {
        return $this->char() === '(';
    }

    public function isConditionEnd()
    {
        return $this->char() === ')';
    }

    public function isEndStatement()
    {
        return $this->char() === ';';
    }

    public function isDelimiter()
    {
        return $this->char() === ',';
    }

    public function isKeyDelimiter()
    {
        return $this->char() === ':';
    }
}