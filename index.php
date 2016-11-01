<?php

error_reporting(E_ALL);

// Core
require 'core/Scope.php';
require 'core/Source.php';
require 'core/Constants.php';
require 'core/Variables.php';
require 'core/Functions.php';
require 'core/Exceptions.php';
require 'core/Interpreter.php';

// Language
require 'lang/Stack.php';
require 'lang/Operators.php';
require 'lang/Variables.php';
require 'lang/VariableGroup.php';
require 'lang/CreateVariable.php';
require 'lang/CreateFunction.php';
require 'lang/CreateCondition.php';
require 'lang/VariableReference.php';
require 'lang/ArgumentVariable.php';
require 'lang/CallFunction.php';
require 'lang/AssignValue.php';
require 'lang/ReturnValue.php';
require 'lang/CreateStatement.php';
require 'lang/ObjectAction.php';
require 'lang/GetObjectProperty.php';
require 'lang/SetObjectProperty.php';

// Functions
require 'functions.php';

$file = $_GET['file'];

$variables = array();

$functions = array();

$callCount = 0;

$start = microtime(TRUE);

if ( file_exists('cache/' . $file) && filemtime('cache/' . $file) > filemtime($file) )
{
    $cache = file_get_contents('cache/' . $file);

    $result = unserialize($cache);
}
else
{
    /*
    $intCount = 0;

    $scope = new Scope();

    $scopeItem = new ScopeItem('global');

    $scope->push($scopeItem);
    */

    $source = new Source(file_get_contents($file));

    $interpreter = new Interpreter($source/*, $scope*/);

    $result = $interpreter->start(GET_GLOBAL_STACK);

    // Cache
    /*
    $resource = fopen('cache/' . $file, 'w+');
    fwrite($resource, serialize($result));
    fclose($resource);
    */
}

$end = microtime(TRUE);

var_dump("Performance: ". ($end - $start));
var_dump("Statement Call Count:". $callCount);

exit;

var_dump($result); exit;

foreach($result->get() as $action)
{
    if ( $action instanceof CreateVariable )
    {
        var_dump($action->getName(), $action->getValue());
    }
}