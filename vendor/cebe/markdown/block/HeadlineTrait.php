<?php


namespace cebe\markdown\block;


trait HeadlineTrait
{
	
	protected function identifyHeadline($line, $lines, $current)
	{
		return (
			// heading with #
			$line[0] === '#' && !preg_match('/^#\d+/', $line)
			||
			// underlined headline
			!empty($lines[$current + 1]) &&
			(($l = $lines[$current + 1][0]) === '=' || $l === '-') &&
			preg_match('/^(\-+|=+)\s*$/', $lines[$current + 1])
		);
	}

	
	protected function consumeHeadline($lines, $current)
	{
		if ($lines[$current][0] === '#') {
			// ATX headline
			$level = 1;
			while (isset($lines[$current][$level]) && $lines[$current][$level] === '#' && $level < 6) {
				$level++;
			}
			$block = [
				'headline',
				'content' => $this->parseInline(trim($lines[$current], "# \t")),
				'level' => $level,
			];
			return [$block, $current];
		} else {
			// underlined headline
			$block = [
				'headline',
				'content' => $this->parseInline($lines[$current]),
				'level' => $lines[$current + 1][0] === '=' ? 1 : 2,
			];
			return [$block, $current + 1];
		}
	}

	
	protected function renderHeadline($block)
	{
		$tag = 'h' . $block['level'];
		return "<$tag>" . $this->renderAbsy($block['content']) . "</$tag>\n";
	}

	abstract protected function parseInline($text);
	abstract protected function renderAbsy($absy);
}
