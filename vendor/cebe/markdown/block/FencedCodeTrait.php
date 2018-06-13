<?php


namespace cebe\markdown\block;


trait FencedCodeTrait
{
	use CodeTrait;

	
	protected function identifyFencedCode($line)
	{
		return ($l = $line[0]) === '`' && strncmp($line, '```', 3) === 0 ||
				$l === '~' && strncmp($line, '~~~', 3) === 0;
	}

	
	protected function consumeFencedCode($lines, $current)
	{
		// consume until ```
		$line = rtrim($lines[$current]);
		$fence = substr($line, 0, $pos = strrpos($line, $line[0]) + 1);
		$language = substr($line, $pos);
		$content = [];
		for ($i = $current + 1, $count = count($lines); $i < $count; $i++) {
			if (rtrim($line = $lines[$i]) !== $fence) {
				$content[] = $line;
			} else {
				break;
			}
		}
		$block = [
			'code',
			'content' => implode("\n", $content),
		];
		if (!empty($language)) {
			$block['language'] = $language;
		}
		return [$block, $i];
	}
}
