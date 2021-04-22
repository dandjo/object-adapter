<?php

require_once 'src/Annotation/AnnotatedProperty.php';
require_once 'src/Annotation/PropertyAnnotationTrait.php';
require_once 'src/ObjectAdapterInterface.php';
require_once 'src/ObjectAdapter.php';
require_once 'src/NullAdapter.php';

class MyAdapterTest extends \Dandjo\ObjectAdapter\ObjectAdapter
{

    /**
     * @property\getter firstProp
     */
    public function getFirstProp()
    {
        return 'first';
    }

    /**
     * @property\getter otherProp
     */
    public function getOtherProp()
    {
        return 'other';
    }

    /**
     * @property\getter unsetProp
     */
    public function getUnsetProp()
    {
        return 'unset';
    }

}

$targetObject = new stdClass();
$targetObject->prop = 'test';
$targetObject->unsetLegacyProp = 'legacy';

$myAdapter = new MyAdapterTest($targetObject);
unset($myAdapter['unsetProp']);
unset($myAdapter['unsetLegacyProp']);

var_dump([
    $myAdapter->prop === 'test',
    $myAdapter->firstProp === 'first',
    $myAdapter->otherProp === 'other',
    isset($myAdapter['wrongProp']) === false,
    isset($myAdapter['prop']) === true,
    $myAdapter->wrongProp instanceof \Dandjo\ObjectAdapter\NullAdapter,
    $myAdapter->unsetProp instanceof \Dandjo\ObjectAdapter\NullAdapter,
    $myAdapter->unsetLegacyProp instanceof \Dandjo\ObjectAdapter\NullAdapter,
    $myAdapter->chained->wrong->prop instanceof \Dandjo\ObjectAdapter\NullAdapter,
]);