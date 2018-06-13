<?php


namespace cebe\markdown;
use ReflectionMethod;


abstract class Parser
{
	
	public $maximumNestingLevel = 32;

	
	protected $context = [];
	
	protected $escapeCharacters = [
		'\\', // backslash
	];

	private $_depth = 0;


	
	public function parse($text)
	{
		$this->prepare();

		if (ltrim($text) === '') {
			return '';
		}

		$text = str_replace(["\r\n", "\n\r", "\r"], "\n", $text);

		$this->prepareMarkers($text);

		$absy = $this->parseBlocks(explode("\n", $text));
		$markup = $this->renderAbsy($absy);

		$this->cleanup();
		return $markup;
	}

	
	public function parseParagraph($text)
	{
		$this->prepare();

		if (ltrim($text) === '') {
			return '';
		}

		$text = str_replace(["\r\n", "\n\r", "\r"], "\n", $text);

		$this->prepareMarkers($text);

		$absy = $this->parseInline($text);
		$markup = $this->renderAbsy($absy);

		$this->cleanup();
		return $markup;
	}

	
	protected function prepare()
	{
	}

	
	protected function cleanup()
	{
	}


	// block parsing

	private $_blockTypes;

	
	protected function blockTypes()
	{
		if ($this->_blockTypes === null) {
			// detect block types via "identify" functions
			$reflection = new \ReflectionClass($this);
			$this->_blockTypes = array_filter(array_map(function($method) {
				$name = $method->getName();
				return strncmp($name, 'identify', 8) === 0 ? strtolower(substr($name, 8)) : false;
			}, $reflection->getMethods(ReflectionMethod::IS_PROTECTED)));

			sort($this->_blockTypes);
		}
		return $this->_blockTypes;
	}

	
	protected function detectLineType($lines, $current)
	{
		$line = $lines[$current];
		$blockTypes = $this->blockTypes();
		foreach($blockTypes as $blockType) {
			if ($this->{'identify' . $blockType}($line, $lines, $current)) {
				return $blockType;
			}
		}
		// consider the line a normal paragraph if no other block type matches
		return 'paragraph';
	}

	
	protected function parseBlocks($lines)
	{
		if ($this->_depth >= $this->maximumNestingLevel) {
			// maximum depth is reached, do not parse input
			return [['text', implode("\n", $lines)]];
		}
		$this->_depth++;

		$blocks = [];

		// convert lines to blocks
		for ($i = 0, $count = count($lines); $i < $count; $i++) {
			$line = $lines[$i];
			if ($line !== '' && rtrim($line) !== '') { // skip empty lines
				// identify a blocks beginning and parse the content
				list($block, $i) = $this->parseBlock($lines, $i);
				if ($block !== false) {
					$blocks[] = $block;
				}
			}
		}

		$this->_depth--;

		return $blocks;
	}

	
	protected function parseBlock($lines, $current)
	{
		// identify block type for this line
		$blockType = $this->detectLineType($lines, $current);

		// call consume method for the detected block type to consume further lines
		return $this->{'consume' . $blockType}($lines, $current);
	}

	protected function renderAbsy($blocks)
	{
		$output = '';
		foreach ($blocks as $block) {
			array_unshift($this->context, $block[0]);
			$output .= $this->{'render' . $block[0]}($block);
			array_shift($this->context);
		}
		return $output;
	}

	
	protected function consumeParagraph($lines, $current)
	{
		// consume until newline
		$content = [];
		for ($i = $current, $count = count($lines); $i < $count; $i++) {
			if (ltrim($lines[$i]) !== '') {
				$content[] = $lines[$i];
			} else {
				break;
			}
		}
		$block = [
			'paragraph',
			'content' => $this->parseInline(implode("\n", $content)),
		];
		return [$block, --$i];
	}

	
	protected function renderParagraph($block)
	{
		return '<p>' . $this->renderAbsy($block['content']) . "</p>\n";
	}


	// inline parsing


	
	private $_inlineMarkers = [];

	
	protected function inlineMarkers()
	{
		$markers = [];
		// detect "parse" functions
		$reflection = new \ReflectionClass($this);
		foreach($reflection->getMethods(ReflectionMethod::IS_PROTECTED) as $method) {
			$methodName = $method->getName();
			if (strncmp($methodName, 'parse', 5) === 0) {
				preg_match_all('/@marker ([^\s]+)/', $method->getDocComment(), $matches);
				foreach($matches[1] as $match) {
					$markers[$match] = $methodName;
				}
			}
		}
		return $markers;
	}

	
	protected function prepareMarkers($text)
	{
		$this->_inlineMarkers = [];
		foreach ($this->inlineMarkers() as $marker => $method) {
			if (strpos($text, $marker) !== false) {
				$m = $marker[0];
				// put the longest marker first
				if (isset($this->_inlineMarkers[$m])) {
					reset($this->_inlineMarkers[$m]);
					if (strlen($marker) > strlen(key($this->_inlineMarkers[$m]))) {
						$this->_inlineMarkers[$m] = array_merge([$marker => $method], $this->_inlineMarkers[$m]);
						continue;
					}
				}
				$this->_inlineMarkers[$m][$marker] = $method;
			}
		}
	}

	
	protected function parseInline($text)
	{
		if ($this->_depth >= $this->maximumNestingLevel) {
			// maximum depth is reached, do not parse input
			return [['text', $text]];
		}
		$this->_depth++;

		$markers = implode('', array_keys($this->_inlineMarkers));

		$paragraph = [];

		while (!empty($markers) && ($found = strpbrk($text, $markers)) !== false) {

			$pos = strpos($text, $found);

			// add the text up to next marker to the paragraph
			if ($pos !== 0) {
				$paragraph[] = ['text', substr($text, 0, $pos)];
			}
			$text = $found;

			$parsed = false;
			foreach ($this->_inlineMarkers[$text[0]] as $marker => $method) {
				if (strncmp($text, $marker, strlen($marker)) === 0) {
					// parse the marker
					array_unshift($this->context, $method);
					list($output, $offset) = $this->$method($text);
					array_shift($this->context);

					$paragraph[] = $output;
					$text = substr($text, $offset);
					$parsed = true;
					break;
				}
			}
			if (!$parsed) {
				$paragraph[] = ['text', substr($text, 0, 1)];
				$text = substr($text, 1);
			}
		}

		$paragraph[] = ['text', $text];

		$this->_depth--;

		return $paragraph;
	}

	
	protected function parseEscape($text)
	{
		if (isset($text[1]) && in_array($text[1], $this->escapeCharacters)) {
			return [['text', $text[1]], 2];
		}
		return [['text', $text[0]], 1];
	}

	
	protected function renderText($block)
	{
		return $block[1];
	}
}
