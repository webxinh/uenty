<?php


namespace cebe\markdown\inline;


trait StrikeoutTrait
{
	
	protected function parseStrike($markdown)
	{
		if (preg_match('/^~~(.+?)~~/', $markdown, $matches)) {
			return [
				[
					'strike',
					$this->parseInline($matches[1])
				],
				strlen($matches[0])
			];
		}
		return [['text', $markdown[0] . $markdown[1]], 2];
	}

	protected function renderStrike($block)
	{
		return '<del>' . $this->renderAbsy($block[1]) . '</del>';
	}
}
