<?php

namespace Faker\Provider\en_CA;

use Faker\Generator;
use Faker\Provider\en_CA\Address;

class AddressTest extends \PHPUnit_Framework_TestCase
{

  
  private $faker;

  public function setUp()
  {
    $faker = new Generator();
    $faker->addProvider(new Address($faker));
    $this->faker = $faker;
  }

  
  public function testProvince()
  {
    $province = $this->faker->province();
    $this->assertNotEmpty($province);
    $this->assertInternalType('string', $province);
    $this->assertRegExp('/[A-Z][a-z]+/', $province);
  }

  
  public function testProvinceAbbr()
  {
    $provinceAbbr = $this->faker->provinceAbbr();
    $this->assertNotEmpty($provinceAbbr);
    $this->assertInternalType('string', $provinceAbbr);
    $this->assertRegExp('/^[A-Z]{2}$/', $provinceAbbr);
  }

  
  public function testPostcodeLetter()
  {
    $postcodeLetter = $this->faker->randomPostcodeLetter();
    $this->assertNotEmpty($postcodeLetter);
    $this->assertInternalType('string', $postcodeLetter);
    $this->assertRegExp('/^[A-Z]{1}$/', $postcodeLetter);
  }

  
  public function testPostcode()
  {
    $postcode = $this->faker->postcode();
    $this->assertNotEmpty($postcode);
    $this->assertInternalType('string', $postcode);
    $this->assertRegExp('/^[A-Za-z]\d[A-Za-z][ -]?\d[A-Za-z]\d$/', $postcode);
  }
}

?>
