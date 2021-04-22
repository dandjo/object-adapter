<?php

require_once 'src/Annotation/AnnotatedProperty.php';
require_once 'src/Annotation/PropertyAnnotationTrait.php';
require_once 'src/ObjectAdapterInterface.php';
require_once 'src/ObjectAdapter.php';
require_once 'src/NullAdapter.php';

class MyAdapterTest extends \Dandjo\ObjectAdapter\ObjectAdapter
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
$targetObject->myUnsetLegacyProp = 'legacy';

$myAdapter = new MyAdapterTest($targetObject);
unset($myAdapter['myUnsetProp']);
unset($myAdapter['myUnsetLegacyProp']);

var_dump([
    $myAdapter->myProp === 'test',
    $myAdapter->myFirstProp === 'first',
    $myAdapter->myOtherProp === 'other',
    isset($myAdapter['wrongProp']) === false,
    isset($myAdapter['myProp']) === true,
    $myAdapter->get('myOtherProp') === 'other',
    $myAdapter->get('wrongProp') instanceof \Dandjo\ObjectAdapter\NullAdapter,
    $myAdapter->myUnsetProp instanceof \Dandjo\ObjectAdapter\NullAdapter,
    $myAdapter->myUnsetLegacyProp instanceof \Dandjo\ObjectAdapter\NullAdapter,
    $myAdapter->get('chained.wrong.prop') instanceof \Dandjo\ObjectAdapter\NullAdapter,
    $myAdapter->get('.') instanceof \Dandjo\ObjectAdapter\NullAdapter,
]);