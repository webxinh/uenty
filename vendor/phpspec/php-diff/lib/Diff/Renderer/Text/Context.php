<?php


require_once dirname(__FILE__).'/../Abstract.php';

class Diff_Renderer_Text_Context extends Diff_Renderer_Abstract
{
	
	private $tagMap = array(
		'insert' => '+',
		'delete' => '-',
		'replace' => '!',
		'equal' => ' '
	);

	
	public function render()
	{
		$diff = '';
		$opCodes = $this->diff->getGroupedOpcodes();
		foreach($opCodes as $group) {
			$diff .= "***************\n";
			$lastItem = count($group)-1;
			$i1 = $group[0][1];
			$i2 = $group[$lastItem][2];
			$j1 = $group[0][3];
			$j2 = $group[$lastItem][4];

			if($i2 - $i1 >= 2) {
				$diff .= '*** '.($group[0][1] + 1).','.$i2." ****".PHP_EOL;
			}
			else {
				$diff .= '*** '.$i2." ****\n";
			}

			if($j2 - $j1 >= 2) {
				$separator = '--- '.($j1 + 1).','.$j2." ----".PHP_EOL;
			}
			else {
				$separator = '--- '.$j2." ----".PHP_EOL;
			}

			$hasVisible = false;
			foreach($group as $code) {
				if($code[0] == 'replace' || $code[0] == 'delete') {
					$hasVisible = true;
					break;
				}
			}

			if($hasVisible) {
				foreach($group as $code) {
					list($tag, $i1, $i2, $j1, $j2) = $code;
					if($tag == 'insert') {
						continue;
					}
					$diff .= $this->tagMap[$tag].' '.implode(PHP_EOL.$this->tagMap[$tag].' ', $this->diff->GetA($i1, $i2)).PHP_EOL;
				}
			}

			$hasVisible = false;
			foreach($group as $code) {
				if($code[0] == 'replace' || $code[0] == 'insert') {
					$hasVisible = true;
					break;
				}
			}

			$diff .= $separator;

			if($hasVisible) {
				foreach($group as $code) {
					list($tag, $i1, $i2, $j1, $j2) = $code;
					if($tag == 'delete') {
						continue;
					}
					$diff .= $this->tagMap[$tag].' '.implode(PHP_EOL.$this->tagMap[$tag].' ', $this->diff->GetB($j1, $j2)).PHP_EOL;
				}
			}
		}
		return $diff;
	}
}