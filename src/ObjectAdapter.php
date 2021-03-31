<?php


namespace Dandjo\ObjectAdapter;


use ArrayAccess;
use stdClass;


/**
 * Class ObjectAdapter.
 * @package Dandjo\ObjectAdapter
 */
class ObjectAdapter implements ArrayAccess
{

	/**
	 * @var object
	 */
	public $targetObject;

	/**
	 * @param $object
	 * @return mixed
	 */
	public static function create($object)
	{
		return new static($object);
	}

	/**
	 * @param array $objects
	 * @return array
	 */
	public static function createArray(array $objects): array
	{
		return array_map(function ($object) {
			return static::create($object);
		}, $objects);
	}

	/**
	 * ObjectAdapter constructor.
	 * @param $object
	 */
	public function __construct($object)
	{
		assert(is_object($object));
		$this->targetObject = $object;
	}

	/**
	 * @param $dottedPath
	 * @param mixed $default
	 * @return mixed
	 */
	public function get($dottedPath, $default = null)
	{
		$properties = preg_split('/\./', $dottedPath);
		$property = array_shift($properties);
		if (is_a($this->{$property}, self::class)) {
			return $this->{$property}->get(implode('.', $properties), $default);
		}
		return $this->{$property} ?: $default;
	}

	/**
	 * @param $property
	 *
	 * @return mixed|object
	 */
	public function __get($property)
	{
		if ($property === 'targetObject') {
			return $this->targetObject;
		}
		if (method_exists($this, 'get' . ucfirst($property))) {
			return $this->{'get' . ucfirst($property)}();
		}
		return $this->targetObject->{$property} ?? self::create(new stdClass());
	}

	/**
	 * @param $property
	 * @param $value
	 * @return $this
	 */
	public function __set($property, $value): ObjectAdapter
	{
		if ($property === 'targetObject') {
			$this->targetObject = $value;
			return $this;
		}
		if (method_exists($this, 'set' . ucfirst($property))) {
			$this->{'set' . ucfirst($property)}($value);
			return $this;
		}
		$this->targetObject->{$property} = $value;
		return $this;
	}

	/**
	 * @param $property
	 * @return bool
	 */
	public function __isset($property): bool
	{
		return property_exists($this->targetObject, $property);
	}

	/**
	 * @param $property
	 */
	public function __unset($property)
	{
		unset($this->targetObject->{$property});
	}

	/**
	 * Whether a offset exists
	 * @param $offset
	 * @return bool
	 */
	public function offsetExists($offset): bool
	{
		return $this->__isset($offset);
	}

	/**
	 * Offset to retrieve
	 * @param $offset
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		return $this->__get($offset);
	}

	/**
	 * Offset to set
	 * @param $offset
	 * @param $value
	 */
	public function offsetSet($offset, $value)
	{
		$this->__set($offset, $value);
	}

	/**
	 * Offset to unset
	 * @param $offset
	 */
	public function offsetUnset($offset)
	{
		$this->__unset($offset);
	}

}
