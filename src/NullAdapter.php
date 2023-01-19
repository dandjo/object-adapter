<?php


namespace Dandjo\ObjectAdapter;


use stdClass;


/**
 * Class NullAdapter.
 * @package Dandjo\ObjectAdapter
 */
class NullAdapter extends ObjectAdapter
{

	/**
	 * NullAdapter constructor.
	 */
	public function __construct()
	{
		parent::__construct(new stdClass());
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return '';
	}

}
