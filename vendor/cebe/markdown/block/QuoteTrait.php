<?php


namespace cebe\markdown\block;


trait QuoteTrait
{
	
	protected function identifyQuote($line)
	{
		return $line[0] === '>' && (!isset($line[1]) || ($l1 = $line[1]) === ' ' || $l1 === "\t");
	}

	
	protected function consumeQuote($lines, $current)
	{
		// consume until newline
		$content = [];
		for ($i = $current, $count = count($lines); $i < $count; $i++) {
			$line = $lines[$i];
			if (ltrim($line) !== '') {
				if ($line[0] == '>' && !isset($line[1])) {
					$line = '';
				} elseif (strncmp($line, '> ', 2) === 0) {
					$line = substr($line, 2);
				}
				$content[] = $line;
			} else {
				break;
			}
		}

		$block = [
			'quote',
			'content' => $this->parseBlocks($content),
			'simple' => true,
		];
		return [$block, $i];
	}


	
	protected function renderQuote($block)
	{
		return '<blockquote>' . $this->renderAbsy($block['content']) . "</blockquote>\n";
	}

	abstract protected function parseBlocks($lines);
	abstract protected function renderAbsy($absy);
}
