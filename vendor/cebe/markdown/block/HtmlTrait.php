<?php


namespace cebe\markdown\block;


trait HtmlTrait
{
	
	protected $inlineHtmlElements = [
		'a', 'abbr', 'acronym',
		'b', 'basefont', 'bdo', 'big', 'br', 'button', 'blink',
		'cite', 'code',
		'del', 'dfn',
		'em',
		'font',
		'i', 'img', 'ins', 'input', 'iframe',
		'kbd',
		'label', 'listing',
		'map', 'mark',
		'nobr',
		'object',
		'q',
		'rp', 'rt', 'ruby',
		's', 'samp', 'script', 'select', 'small', 'spacer', 'span', 'strong', 'sub', 'sup',
		'tt', 'var',
		'u',
		'wbr',
		'time',
	];
	
	protected $selfClosingHtmlElements = [
		'br', 'hr', 'img', 'input', 'nobr',
	];

	
	protected function identifyHtml($line, $lines, $current)
	{
		if ($line[0] !== '<' || isset($line[1]) && $line[1] == ' ') {
			return false; // no html tag
		}

		if (strncmp($line, '<!--', 4) === 0) {
			return true; // a html comment
		}

		$gtPos = strpos($lines[$current], '>');
		$spacePos = strpos($lines[$current], ' ');
		if ($gtPos === false && $spacePos === false) {
			return false; // no html tag
		} elseif ($spacePos === false) {
			$tag = rtrim(substr($line, 1, $gtPos - 1), '/');
		} else {
			$tag = rtrim(substr($line, 1, min($gtPos, $spacePos) - 1), '/');
		}

		if (!ctype_alnum($tag) || in_array(strtolower($tag), $this->inlineHtmlElements)) {
			return false; // no html tag or inline html tag
		}
		return true;
	}

	
	protected function consumeHtml($lines, $current)
	{
		$content = [];
		if (strncmp($lines[$current], '<!--', 4) === 0) { // html comment
			for ($i = $current, $count = count($lines); $i < $count; $i++) {
				$line = $lines[$i];
				$content[] = $line;
				if (strpos($line, '-->') !== false) {
					break;
				}
			}
		} else {
			$tag = rtrim(substr($lines[$current], 1, min(strpos($lines[$current], '>'), strpos($lines[$current] . ' ', ' ')) - 1), '/');
			$level = 0;
			if (in_array($tag, $this->selfClosingHtmlElements)) {
				$level--;
			}
			for ($i = $current, $count = count($lines); $i < $count; $i++) {
				$line = $lines[$i];
				$content[] = $line;
				$level += substr_count($line, "<$tag") - substr_count($line, "</$tag>") - substr_count($line, "/>");
				if ($level <= 0) {
					break;
				}
			}
		}
		$block = [
			'html',
			'content' => implode("\n", $content),
		];
		return [$block, $i];
	}

	
	protected function renderHtml($block)
	{
		return $block['content'] . "\n";
	}

	
	protected function parseEntity($text)
	{
		// html entities e.g. &copy; &#169; &#x00A9;
		if (preg_match('/^&#?[\w\d]+;/', $text, $matches)) {
			return [['inlineHtml', $matches[0]], strlen($matches[0])];
		} else {
			return [['text', '&amp;'], 1];
		}
	}

	
	protected function renderInlineHtml($block)
	{
		return $block[1];
	}

	
	protected function parseInlineHtml($text)
	{
		if (strpos($text, '>') !== false) {
			if (preg_match('~^</?(\w+\d?)( .*?)?>~s', $text, $matches)) {
				// HTML tags
				return [['inlineHtml', $matches[0]], strlen($matches[0])];
			} elseif (preg_match('~^<!--.*?-->~s', $text, $matches)) {
				// HTML comments
				return [['inlineHtml', $matches[0]], strlen($matches[0])];
			}
		}
		return [['text', '&lt;'], 1];
	}

	
	protected function parseGt($text)
	{
		return [['text', '&gt;'], 1];
	}
}
