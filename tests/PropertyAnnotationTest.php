<?php

require_once 'src/Annotation/AnnotatedProperty.php';
require_once 'src/Annotation/PropertyAnnotationTrait.php';

class PropertyAnnotationTest
{

    use \Dandjo\ObjectAdapter\Annotation\PropertyAnnotationTrait;

    public function __construct()
    {
        $this->initProperties();
    }

    public $mySecondProperty = 'my_second_one';

    /**
     * @property\getter myFirstProperty
     */
    public function getMyFirstProperty()
    {
        return 'my_property';
    }

}

$myPropertyAnnotationTest = new PropertyAnnotationTest();

var_dump([
    $myPropertyAnnotationTest->myFirstProperty === 'my_property',
    $myPropertyAnnotationTest->mySecondProperty === 'my_second_one',
    isset($myPropertyAnnotationTest->myFirstProperty) === true,
    isset($myPropertyAnnotationTest->mySecondProperty) === true,
    $myPropertyAnnotationTest->myInvalidProperty === NULL,
]);