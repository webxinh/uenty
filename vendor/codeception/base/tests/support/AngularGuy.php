<?php
use Codeception\Util\JsonArray;
use Codeception\Util\Shared\Asserts;


class AngularGuy extends \Codeception\Actor
{
    use Asserts;
    use _generated\AngularGuyActions;

    public function seeInFormResult($expected)
    {
        $jsonArray = new JsonArray($this->grabTextFrom(['id' => 'data']));
        $this->assertTrue($jsonArray->containsArray($expected), var_export($jsonArray->toArray(), true));
    }

    public function submit()
    {
        $this->click('Submit');
    }
}
