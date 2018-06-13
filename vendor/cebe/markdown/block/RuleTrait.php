<?php


namespace cebe\markdown\block;


trait RuleTrait
{
	
	protected function identifyHr($line)
	{
		// at least 3 of -, * or _ on one line make a hr
		return (($l = $line[0]) === ' ' || $l === '-' || $l === '*' || $l === '_') && preg_match('/^ {0,3}([\-\*_])\s*\1\s*\1(\1|\s)*$/', $line);
	}

	
	protected function consumeHr($lines, $current)
	{
		return [['hr'], $current];
	}

	
	protected function renderHr($block)
	{
		return $this->html5 ? "<hr>\n" : "<hr />\n";
	}

} 