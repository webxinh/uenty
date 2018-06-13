<?php


abstract class Diff_Renderer_Abstract
{
	
	public $diff;

	
	protected $defaultOptions = array();

	
	protected $options = array();

	
	public function __construct(array $options = array())
	{
		$this->setOptions($options);
	}

	
	public function setOptions(array $options)
	{
		$this->options = array_merge($this->defaultOptions, $options);
	}
}