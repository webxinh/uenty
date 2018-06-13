<?php


class Diff_SequenceMatcher
{
	
	private $junkCallback = null;

	
	private $a = null;

	
	private $b = null;

	
	private $junkDict = array();

	
	private $b2j = array();

	private $options = array();

    private $matchingBlocks = null;
    private $opCodes = null;
    private $fullBCount = null;

	private $defaultOptions = array(
		'ignoreNewLines' => false,
		'ignoreWhitespace' => false,
		'ignoreCase' => false
	);

	
	public function __construct($a, $b, $junkCallback=null, $options)
	{
		$this->a = null;
		$this->b = null;
		$this->junkCallback = $junkCallback;
		$this->setOptions($options);
		$this->setSequences($a, $b);
	}

	
	public function setOptions($options)
	{
		$this->options = array_merge($this->defaultOptions, $options);
	}

	
	public function setSequences($a, $b)
	{
		$this->setSeq1($a);
		$this->setSeq2($b);
	}

	
	public function setSeq1($a)
	{
		if(!is_array($a)) {
			$a = str_split($a);
		}
		if($a == $this->a) {
			return;
		}

		$this->a= $a;
		$this->matchingBlocks = null;
		$this->opCodes = null;
	}

	
	public function setSeq2($b)
	{
		if(!is_array($b)) {
			$b = str_split($b);
		}
		if($b == $this->b) {
			return;
		}

		$this->b = $b;
		$this->matchingBlocks = null;
		$this->opCodes = null;
		$this->fullBCount = null;
		$this->chainB();
	}

	
	private function chainB()
	{
		$length = count ($this->b);
		$this->b2j = array();
		$popularDict = array();

		for($i = 0; $i < $length; ++$i) {
			$char = $this->b[$i];
			if(isset($this->b2j[$char])) {
				if($length >= 200 && count($this->b2j[$char]) * 100 > $length) {
					$popularDict[$char] = 1;
					unset($this->b2j[$char]);
				}
				else {
					$this->b2j[$char][] = $i;
				}
			}
			else {
				$this->b2j[$char] = array(
					$i
				);
			}
		}

		// Remove leftovers
		foreach(array_keys($popularDict) as $char) {
			unset($this->b2j[$char]);
		}

		$this->junkDict = array();
		if(is_callable($this->junkCallback)) {
			foreach(array_keys($popularDict) as $char) {
				if(call_user_func($this->junkCallback, $char)) {
					$this->junkDict[$char] = 1;
					unset($popularDict[$char]);
				}
			}

			foreach(array_keys($this->b2j) as $char) {
				if(call_user_func($this->junkCallback, $char)) {
					$this->junkDict[$char] = 1;
					unset($this->b2j[$char]);
				}
			}
		}
	}

	
	private function isBJunk($b)
	{
		if(isset($this->junkDict[$b])) {
			return true;
		}

		return false;
	}

	
	public function findLongestMatch($alo, $ahi, $blo, $bhi)
	{
		$a = $this->a;
		$b = $this->b;

		$bestI = $alo;
		$bestJ = $blo;
		$bestSize = 0;

		$j2Len = array();
		$nothing = array();

		for($i = $alo; $i < $ahi; ++$i) {
			$newJ2Len = array();
			$jDict = $this->arrayGetDefault($this->b2j, $a[$i], $nothing);
			foreach($jDict as $j) {
				if($j < $blo) {
					continue;
				}
				else if($j >= $bhi) {
					break;
				}

				$k = $this->arrayGetDefault($j2Len, $j -1, 0) + 1;
				$newJ2Len[$j] = $k;
				if($k > $bestSize) {
					$bestI = $i - $k + 1;
					$bestJ = $j - $k + 1;
					$bestSize = $k;
				}
			}

			$j2Len = $newJ2Len;
		}

		while($bestI > $alo && $bestJ > $blo && !$this->isBJunk($b[$bestJ - 1]) &&
			!$this->linesAreDifferent($bestI - 1, $bestJ - 1)) {
				--$bestI;
				--$bestJ;
				++$bestSize;
		}

		while($bestI + $bestSize < $ahi && ($bestJ + $bestSize) < $bhi &&
			!$this->isBJunk($b[$bestJ + $bestSize]) && !$this->linesAreDifferent($bestI + $bestSize, $bestJ + $bestSize)) {
				++$bestSize;
		}

		while($bestI > $alo && $bestJ > $blo && $this->isBJunk($b[$bestJ - 1]) &&
			!$this->linesAreDifferent($bestI - 1, $bestJ - 1)) {
				--$bestI;
				--$bestJ;
				++$bestSize;
		}

		while($bestI + $bestSize < $ahi && $bestJ + $bestSize < $bhi &&
			$this->isBJunk($b[$bestJ + $bestSize]) && !$this->linesAreDifferent($bestI + $bestSize, $bestJ + $bestSize)) {
					++$bestSize;
		}

		return array(
			$bestI,
			$bestJ,
			$bestSize
		);
	}

	
	public function linesAreDifferent($aIndex, $bIndex)
	{
		$lineA = $this->a[$aIndex];
		$lineB = $this->b[$bIndex];

		if($this->options['ignoreWhitespace']) {
			$replace = array("\t", ' ');
			$lineA = str_replace($replace, '', $lineA);
			$lineB = str_replace($replace, '', $lineB);
		}

		if($this->options['ignoreCase']) {
			$lineA = strtolower($lineA);
			$lineB = strtolower($lineB);
		}

		if($lineA != $lineB) {
			return true;
		}

		return false;
	}

	
	public function getMatchingBlocks()
	{
		if(!empty($this->matchingBlocks)) {
			return $this->matchingBlocks;
		}

		$aLength = count($this->a);
		$bLength = count($this->b);

		$queue = array(
			array(
				0,
				$aLength,
				0,
				$bLength
			)
		);

		$matchingBlocks = array();
		while(!empty($queue)) {
			list($alo, $ahi, $blo, $bhi) = array_pop($queue);
			$x = $this->findLongestMatch($alo, $ahi, $blo, $bhi);
			list($i, $j, $k) = $x;
			if($k) {
				$matchingBlocks[] = $x;
				if($alo < $i && $blo < $j) {
					$queue[] = array(
						$alo,
						$i,
						$blo,
						$j
					);
				}

				if($i + $k < $ahi && $j + $k < $bhi) {
					$queue[] = array(
						$i + $k,
						$ahi,
						$j + $k,
						$bhi
					);
				}
			}
		}

		usort($matchingBlocks, array($this, 'tupleSort'));

		$i1 = 0;
		$j1 = 0;
		$k1 = 0;
		$nonAdjacent = array();
		foreach($matchingBlocks as $block) {
			list($i2, $j2, $k2) = $block;
			if($i1 + $k1 == $i2 && $j1 + $k1 == $j2) {
				$k1 += $k2;
			}
			else {
				if($k1) {
					$nonAdjacent[] = array(
						$i1,
						$j1,
						$k1
					);
				}

				$i1 = $i2;
				$j1 = $j2;
				$k1 = $k2;
			}
		}

		if($k1) {
			$nonAdjacent[] = array(
				$i1,
				$j1,
				$k1
			);
		}

		$nonAdjacent[] = array(
			$aLength,
			$bLength,
			0
		);

		$this->matchingBlocks = $nonAdjacent;
		return $this->matchingBlocks;
	}

	
	public function getOpCodes()
	{
		if(!empty($this->opCodes)) {
			return $this->opCodes;
		}

		$i = 0;
		$j = 0;
		$this->opCodes = array();

		$blocks = $this->getMatchingBlocks();
		foreach($blocks as $block) {
			list($ai, $bj, $size) = $block;
			$tag = '';
			if($i < $ai && $j < $bj) {
				$tag = 'replace';
			}
			else if($i < $ai) {
				$tag = 'delete';
			}
			else if($j < $bj) {
				$tag = 'insert';
			}

			if($tag) {
				$this->opCodes[] = array(
					$tag,
					$i,
					$ai,
					$j,
					$bj
				);
			}

			$i = $ai + $size;
			$j = $bj + $size;

			if($size) {
				$this->opCodes[] = array(
					'equal',
					$ai,
					$i,
					$bj,
					$j
				);
			}
		}
		return $this->opCodes;
	}

	
	public function getGroupedOpcodes($context=3)
	{
		$opCodes = $this->getOpCodes();
		if(empty($opCodes)) {
			$opCodes = array(
				array(
					'equal',
					0,
					1,
					0,
					1
				)
			);
		}

		if($opCodes[0][0] == 'equal') {
			$opCodes[0] = array(
				$opCodes[0][0],
				max($opCodes[0][1], $opCodes[0][2] - $context),
				$opCodes[0][2],
				max($opCodes[0][3], $opCodes[0][4] - $context),
				$opCodes[0][4]
			);
		}

		$lastItem = count($opCodes) - 1;
		if($opCodes[$lastItem][0] == 'equal') {
			list($tag, $i1, $i2, $j1, $j2) = $opCodes[$lastItem];
			$opCodes[$lastItem] = array(
				$tag,
				$i1,
				min($i2, $i1 + $context),
				$j1,
				min($j2, $j1 + $context)
			);
		}

		$maxRange = $context * 2;
		$groups = array();
		$group = array();
		foreach($opCodes as $code) {
			list($tag, $i1, $i2, $j1, $j2) = $code;
			if($tag == 'equal' && $i2 - $i1 > $maxRange) {
				$group[] = array(
					$tag,
					$i1,
					min($i2, $i1 + $context),
					$j1,
					min($j2, $j1 + $context)
				);
				$groups[] = $group;
				$group = array();
				$i1 = max($i1, $i2 - $context);
				$j1 = max($j1, $j2 - $context);
			}
			$group[] = array(
				$tag,
				$i1,
				$i2,
				$j1,
				$j2
			);
		}

		if(!empty($group) && !(count($group) == 1 && $group[0][0] == 'equal')) {
			$groups[] = $group;
		}

		return $groups;
	}

	
	public function Ratio()
	{
		$matches = array_reduce($this->getMatchingBlocks(), array($this, 'ratioReduce'), 0);
		return $this->calculateRatio($matches, count ($this->a) + count ($this->b));
	}

	
	private function ratioReduce($sum, $triple)
	{
		return $sum + ($triple[count($triple) - 1]);
	}

	
	private function quickRatio()
	{
		if($this->fullBCount === null) {
			$this->fullBCount = array();
			$bLength = count ($this->b);
			for($i = 0; $i < $bLength; ++$i) {
				$char = $this->b[$i];
				$this->fullBCount[$char] = $this->arrayGetDefault($this->fullBCount, $char, 0) + 1;
			}
		}

		$avail = array();
		$matches = 0;
		$aLength = count ($this->a);
		for($i = 0; $i < $aLength; ++$i) {
			$char = $this->a[$i];
			if(isset($avail[$char])) {
				$numb = $avail[$char];
			}
			else {
				$numb = $this->arrayGetDefault($this->fullBCount, $char, 0);
			}
			$avail[$char] = $numb - 1;
			if($numb > 0) {
				++$matches;
			}
		}

		$this->calculateRatio($matches, count ($this->a) + count ($this->b));
	}

	
	private function realquickRatio()
	{
		$aLength = count ($this->a);
		$bLength = count ($this->b);

		return $this->calculateRatio(min($aLength, $bLength), $aLength + $bLength);
	}

	
	private function calculateRatio($matches, $length=0)
	{
		if($length) {
			return 2 * ($matches / $length);
		}
		else {
			return 1;
		}
	}

	
	private function arrayGetDefault($array, $key, $default)
	{
		if(isset($array[$key])) {
			return $array[$key];
		}
		else {
			return $default;
		}
	}

	
	private function tupleSort($a, $b)
	{
		$max = max(count($a), count($b));
		for($i = 0; $i < $max; ++$i) {
			if($a[$i] < $b[$i]) {
				return -1;
			}
			else if($a[$i] > $b[$i]) {
				return 1;
			}
		}

		if(count($a) == count($b)) {
			return 0;
		}
		else if(count($a) < count($b)) {
			return -1;
		}
		else {
			return 1;
		}
	}
}
