<?php


class Diff
{
	
	private $a = null;

	
	private $b = null;

	
	private $groupedCodes = null;

	
	private $defaultOptions = array(
		'context' => 3,
		'ignoreNewLines' => false,
		'ignoreWhitespace' => false,
		'ignoreCase' => false
	);

	
	private $options = array();

	
	public function __construct($a, $b, $options=array())
	{
		$this->a = $a;
		$this->b = $b;

		$this->options = array_merge($this->defaultOptions, $options);
	}

	
	public function render(Diff_Renderer_Abstract $renderer)
	{
		$renderer->diff = $this;
		return $renderer->render();
	}

	
	public function getA($start=0, $end=null)
	{
		if($start == 0 && $end === null) {
			return $this->a;
		}

		if($end === null) {
			$length = 1;
		}
		else {
			$length = $end - $start;
		}

		return array_slice($this->a, $start, $length);

	}

	
	public function getB($start=0, $end=null)
	{
		if($start == 0 && $end === null) {
			return $this->b;
		}

		if($end === null) {
			$length = 1;
		}
		else {
			$length = $end - $start;
		}

		return array_slice($this->b, $start, $length);
	}

	
	public function getGroupedOpcodes()
	{
		if(!is_null($this->groupedCodes)) {
			return $this->groupedCodes;
		}

		require_once dirname(__FILE__).'/Diff/SequenceMatcher.php';
		$sequenceMatcher = new Diff_SequenceMatcher($this->a, $this->b, null, $this->options);
		$this->groupedCodes = $sequenceMatcher->getGroupedOpcodes();
		return $this->groupedCodes;
	}
}