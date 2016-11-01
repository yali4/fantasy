<?php

class CreateFunction
{
    protected $name;

    protected $scope;

    protected $arguments;

    protected $returnType;

    protected $stack;

    protected $required;

    public function __construct($name)
    {
        //var_dump($name, $arguments); exit;

        $this->name = $name;

        $this->arguments = array();

        $this->required = 0;

        //var_dump($arguments); exit;
    }

    public function setArguments($arguments)
    {
        $this->arguments = $arguments;

        foreach($arguments as $argument)
        {

            if ( !method_exists($argument, 'getValue') )
            {
                var_dump($this->name, $arguments); exit;
            }


            if ( $argument->getValue() === null )
            {
                $this->required++;
            }
        }
    }

    public function setReturnType($type)
    {
        $this->returnType = $type;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getScope()
    {
        return $this->scope;
    }

    public function getStack()
    {
        return $this->stack;
    }

    public function setScope($scope)
    {
        global $functions;

        $this->scope = $scope;

        $functions[$this->scope][$this->name] = $this;
    }

    public function addStack(Stack $stack)
    {
        $this->stack = $stack;
    }

    public function getResult(array $arguments)
    {
        global $variables;

        //var_dump($this->args, $args); //exit;

        if ( $this->required > count($arguments) )
        {
            throw new Wrong_Parameter();
        }

        foreach($this->arguments as $key => $argument)
        {
            $variable = new CreateVariable('dynamic', $argument->getName());

            $variable->setScope('calling');

            if ( isset($arguments[$key]) )
            {
                if ( $argument->getType() && $arguments[$key]->getType() !== $argument->getType() )
                {
                    throw new Wrong_Parameter_Type();
                }

                $value = clone $arguments[$key];

                $variable->setValue($value, 'global');
            }
            else
            {
                $value = clone $argument->getValue();

                $variable->setValue($value, 'global');
            }
        }

        //var_dump($variables['calling']); //exit;

        $execute = $this->runStack($this->stack, 'calling');

        if ( $execute === null )
        {
            return new NullVariable(null);
        }

        return $execute;
    }

    function runStack(Stack $stack, $scope)
    {
        global $variables, $functions;

        $context = $stack->get();

        while ( $action = array_shift($context) )
        {
            if ($action instanceof CreateVariable)
            {
                $variable = new CreateVariable($action->getType(), $action->getName());

                $variable->setScope($scope);

                if ( $action->getStatement() === null )
                {
                    $variable->setValue(clone $action->getValue(), 'global');
                }
                else
                {
                    $variable->setValue(clone $action->getStatement(), 'global');
                }
            }

            if ( $action instanceof CreateFunction )
            {
                $function = new CreateFunction($action->getName(), $action->getArguments());

                $function->setScope($scope);

                $function->addStack(clone $action->getStack());
            }

            if ( $action instanceof CreateCondition )
            {
                if ( $action->getResult($scope)->isTrue() )
                {
                    $execute = $this->runStack($action->getStack(), $scope);

                    if ( $execute !== null ) return $execute;
                }
            }

            if ( $action instanceof ReturnValue )
            {
                return $action->getResult($scope);
            }
        }

        return null;
    }
}