<?php

function is_start_string($char)
{
    return ($char === '"' || $char === "'");
}

function is_delimiter($char)
{
    return ($char === ',');
}

function is_key_delimiter($char)
{
    return ($char === ':');
}

function is_start_array($char)
{
    return ($char === '[');
}

function is_end_array($char)
{
    return ($char === ']');
}

function is_start_object($char)
{
    return ($char === '{');
}

function is_end_object($char)
{
    return ($char === '}');
}