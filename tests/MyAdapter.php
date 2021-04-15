<?php

require_once 'src/ObjectAdapterInterface.php';
require_once 'src/ObjectAdapter.php';
require_once 'src/NullAdapter.php';
require_once 'src/Reflection/AdapterProperty.php';

class MyAdapter extends \Dandjo\ObjectAdapter\ObjectAdapter
{

    /**
     * @property\getter myFirstProp
     */
    public function getMyProperty1()
    {
        return 'first';
    }

    /**
     * @property\getter myOtherProp
     */
    public function getMyProperty2()
    {
        return 'other';
    }

    /**
     * @property\getter myUnsetProp
     */
    public function getUnsetProperty()
    {
        return 'unset';
    }

}

$targetObject = new stdClass();
$targetObject->myProp = 'test';

$myAdapter = new MyAdapter($targetObject);
unset($myAdapter['myUnsetProp']);

var_dump([
    $myAdapter->myProp === 'test',
    $myAdapter->myFirstProp === 'first',
    $myAdapter->myOtherProp === 'other',
    isset($myAdapter['wrongProp']) === false,
    isset($myAdapter['myProp']) === true,
    $myAdapter->get('myOtherProp') === 'other',
    $myAdapter->get('wrongProp') instanceof \Dandjo\ObjectAdapter\NullAdapter,
    $myAdapter->myUnsetProp instanceof \Dandjo\ObjectAdapter\NullAdapter,
    $myAdapter->get('chained.wrong.prop') instanceof \Dandjo\ObjectAdapter\NullAdapter,
    $myAdapter->get('.') instanceof \Dandjo\ObjectAdapter\NullAdapter,
]);