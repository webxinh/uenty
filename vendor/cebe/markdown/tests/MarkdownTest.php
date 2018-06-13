<?php


namespace cebe\markdown\tests;

use cebe\markdown\Markdown;


class MarkdownTest extends BaseMarkdownTest
{
	public function createMarkdown()
	{
		return new Markdown();
	}

	public function getDataPaths()
	{
		return [
			'markdown-data' => __DIR__ . '/markdown-data',
		];
	}

	public function testEdgeCases()
	{
		$this->assertEquals("<p>&amp;</p>\n", $this->createMarkdown()->parse('&'));
		$this->assertEquals("<p>&lt;</p>\n", $this->createMarkdown()->parse('<'));
	}

	public function testKeepZeroAlive()
	{
		$parser = $this->createMarkdown();

		$this->assertEquals("0", $parser->parseParagraph("0"));
		$this->assertEquals("<p>0</p>\n", $parser->parse("0"));
	}
}
