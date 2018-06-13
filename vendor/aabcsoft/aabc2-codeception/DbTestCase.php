<?php


namespace aabc\codeception;

use aabc\test\InitDbFixture;


class DbTestCase extends TestCase
{
    /**
     * @inheritdoc
     */
    public function globalFixtures()
    {
        return [
            InitDbFixture::className(),
        ];
    }
}
