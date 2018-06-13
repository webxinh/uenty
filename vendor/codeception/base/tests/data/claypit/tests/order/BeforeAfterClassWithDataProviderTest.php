<?php

class BeforeAfterClassWithDataProviderTest extends \Codeception\TestCase\Test
{
	
	public static function setUpSomeSharedFixtures()
	{
		\Codeception\Module\OrderHelper::appendToFile('{');
	}

	
	public function testAbc($letter)
	{
		\Codeception\Module\OrderHelper::appendToFile($letter);
	}

	public static function getAbc()
	{
		return [['A'], ['B'], ['C']];
	}

	
	public static function tearDownSomeSharedFixtures()
	{
		\Codeception\Module\OrderHelper::appendToFile('}');
	}

}
