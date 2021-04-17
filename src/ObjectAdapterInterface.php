<?php


namespace Dandjo\ObjectAdapter;


use ArrayAccess;
use JsonSerializable;
use Traversable;

/**
 * Interface ObjectAdapterInterface.
 * @package Dandjo\ObjectAdapter
 */
interface ObjectAdapterInterface extends Traversable, ArrayAccess, JsonSerializable
{
}