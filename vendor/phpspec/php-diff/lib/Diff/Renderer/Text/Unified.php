<?php


require_once dirname(__FILE__).'/../Abstract.php';

class Diff_Renderer_Text_Unified extends Diff_Renderer_Abstract
{
	
	public function render()
	{
		$diff = '';
		$opCodes = $this->diff->getGroupedOpcodes();
		foreach($opCodes as $group) {
			$lastItem = count($group)-1;
			$i1 = $group[0][1];
			$i2 = $group[$lastItem][2];
			$j1 = $group[0][3];
			$j2 = $group[$lastItem][4];

			if($i1 == 0 && $i2 == 0) {
				$i1 = -1;
				$i2 = -1;
			}

			$diff .= '@@ -'.($i1 + 1).','.($i2 - $i1).' +'.($j1 + 1).','.($j2 - $j1)." @@".PHP_EOL;
			foreach($group as $code) {
				list($tag, $i1, $i2, $j1, $j2) = $code;
				if($tag == 'equal') {
					$diff .= ' '.implode(PHP_EOL." ", $this->diff->GetA($i1, $i2)).PHP_EOL;
				}
				else {
					if($tag == 'replace' || $tag == 'delete') {
						$diff .= '-'.implode(PHP_EOL."-", $this->diff->GetA($i1, $i2)).PHP_EOL;
					}

					if($tag == 'replace' || $tag == 'insert') {
						$diff .= '+'.implode(PHP_EOL."+", $this->diff->GetB($j1, $j2)).PHP_EOL;
					}
				}
			}
		}
		return $diff;
	}
}