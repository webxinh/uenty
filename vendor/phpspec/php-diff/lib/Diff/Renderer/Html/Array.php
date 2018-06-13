<?php


require_once dirname(__FILE__).'/../Abstract.php';

class Diff_Renderer_Html_Array extends Diff_Renderer_Abstract
{
	
	protected $defaultOptions = array(
		'tabSize' => 4
	);

	
	public function render()
	{
		// As we'll be modifying a & b to include our change markers,
		// we need to get the contents and store them here. That way
		// we're not going to destroy the original data
		$a = $this->diff->getA();
		$b = $this->diff->getB();

		$changes = array();
		$opCodes = $this->diff->getGroupedOpcodes();
		foreach($opCodes as $group) {
			$blocks = array();
			$lastTag = null;
			$lastBlock = 0;
			foreach($group as $code) {
				list($tag, $i1, $i2, $j1, $j2) = $code;

				if($tag == 'replace' && $i2 - $i1 == $j2 - $j1) {
					for($i = 0; $i < ($i2 - $i1); ++$i) {
						$fromLine = $a[$i1 + $i];
						$toLine = $b[$j1 + $i];

						list($start, $end) = $this->getChangeExtent($fromLine, $toLine);
						if($start != 0 || $end != 0) {
                            $realEnd = mb_strlen($fromLine) + $end;
                            $fromLine = mb_substr($fromLine, 0, $start)
                                . "\0"
                                . mb_substr($fromLine, $start, $realEnd - $start)
                                . "\1"
                                . mb_substr($fromLine, $realEnd);
                            $realEnd = mb_strlen($toLine) + $end;
                            $toLine = mb_substr($toLine, 0, $start)
                                . "\0"
                                . mb_substr($toLine, $start, $realEnd - $start)
                                . "\1"
                                . mb_substr($toLine, $realEnd);
							$a[$i1 + $i] = $fromLine;
							$b[$j1 + $i] = $toLine;
						}
					}
				}

				if($tag != $lastTag) {
					$blocks[] = array(
						'tag' => $tag,
						'base' => array(
							'offset' => $i1,
							'lines' => array()
						),
						'changed' => array(
							'offset' => $j1,
							'lines' => array()
						)
					);
					$lastBlock = count($blocks)-1;
				}

				$lastTag = $tag;

				if($tag == 'equal') {
					$lines = array_slice($a, $i1, ($i2 - $i1));
					$blocks[$lastBlock]['base']['lines'] += $this->formatLines($lines);
					$lines = array_slice($b, $j1, ($j2 - $j1));
					$blocks[$lastBlock]['changed']['lines'] +=  $this->formatLines($lines);
				}
				else {
					if($tag == 'replace' || $tag == 'delete') {
						$lines = array_slice($a, $i1, ($i2 - $i1));
						$lines = $this->formatLines($lines);
						$lines = str_replace(array("\0", "\1"), array('<del>', '</del>'), $lines);
						$blocks[$lastBlock]['base']['lines'] += $lines;
					}

					if($tag == 'replace' || $tag == 'insert') {
						$lines = array_slice($b, $j1, ($j2 - $j1));
						$lines =  $this->formatLines($lines);
						$lines = str_replace(array("\0", "\1"), array('<ins>', '</ins>'), $lines);
						$blocks[$lastBlock]['changed']['lines'] += $lines;
					}
				}
			}
			$changes[] = $blocks;
		}
		return $changes;
	}

	
	private function getChangeExtent($fromLine, $toLine)
	{
		$start = 0;
		$limit = min(mb_strlen($fromLine), mb_strlen($toLine));
		while($start < $limit && mb_substr($fromLine, $start, 1) == mb_substr($toLine, $start, 1)) {
			++$start;
		}
		$end = -1;
		$limit = $limit - $start;
		while(-$end <= $limit && mb_substr($fromLine, $end, 1) == mb_substr($toLine, $end, 1)) {
			--$end;
		}
		return array(
			$start,
			$end + 1
		);
	}

	
	private function formatLines($lines)
	{
		if ($this->options['tabSize'] !== false) {
			$lines = array_map(array($this, 'ExpandTabs'), $lines);
		}
		$lines = array_map(array($this, 'HtmlSafe'), $lines);
		foreach($lines as &$line) {
			$line = preg_replace_callback('# ( +)|^ #', __CLASS__."::fixSpaces", $line);
		}
		return $lines;
	}

	
	public static function fixSpaces($matches)
	{
		$spaces = isset($matches[1]) ? $matches[1] : '';
		$count = strlen($spaces);
		if($count == 0) {
			return '';
		}

		$div = floor($count / 2);
		$mod = $count % 2;
		return str_repeat('&nbsp; ', $div).str_repeat('&nbsp;', $mod);
	}

	
	private function expandTabs($line)
	{
		return str_replace("\t", str_repeat(' ', $this->options['tabSize']), $line);
	}

	
	private function htmlSafe($string)
	{
		return htmlspecialchars($string, ENT_NOQUOTES, 'UTF-8');
	}
}
