<?php


namespace Dandjo\ObjectAdapter;


use JsonSerializable;
use stdClass;


/**
 * Class JsonObjectAdapter.
 * @package Dandjo\ObjectAdapter
 */
class JsonObjectAdapter extends ObjectAdapter implements JsonSerializable
{

	/**
	 * @param mixed $json
	 * @return mixed
	 */
	public static function create($json)
	{
		return new static($json);
	}

	/**
	 * @param mixed $json
	 * @return array
	 */
	public static function createArray($json): array
	{
		if (is_string($json)) {
			$json = json_decode($json);
		}
		if (empty($json)) {
			$json = [];
		}
		return array_map(function ($object) {
			return static::create($object);
		}, $json);
	}

	/**
	 * JsonObjectAdapter constructor.
	 * @param array $json
	 */
	public function __construct($json)
	{
		if (is_array($json)) {
			$json = json_encode($json);
		}
		if (is_string($json)) {
			$json = json_decode($json);
		}
		if (empty($json)) {
			$json = new stdClass();
		}
		parent::__construct($json);
	}

	/**
	 * Updates values from given json in the target.
	 * @param $json
	 * @return mixed
	 */
	public function update($json)
	{
		if (is_object($json)) {
			$json = json_encode($json);
		}
		if (is_string($json)) {
			$json = json_decode($json, true);
		}
		$thisJson = json_decode(json_encode($this), true);
		$updatedJson = array_replace_recursive($thisJson, $json);
		$this->__construct($updatedJson);
		return $this;
	}

	/**
	 * Specify data which should be serialized to JSON.
	 * @return mixed data which can be serialized by json_encode,
	 */
	public function jsonSerialize()
	{
		return $this->targetObject;
	}

}
