<?php

class Stack
{
    protected $queue = array();

    public function add($action)
    {
        $this->queue[] = $action;
    }

    public function get()
    {
        return $this->queue;
    }
}