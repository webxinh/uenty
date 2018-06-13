<?php


namespace Faker\Test\Provider;

use Faker;


class ProviderOverrideTest extends \PHPUnit_Framework_TestCase
{
    
    const TEST_STRING_REGEX = '/.+/u';

    
    const TEST_EMAIL_REGEX = '/^(.+)@(.+)$/ui';

    
    public function testAddress($locale = null)
    {
        $faker = Faker\Factory::create($locale);

        $this->assertRegExp(static::TEST_STRING_REGEX, $faker->city);
        $this->assertRegExp(static::TEST_STRING_REGEX, $faker->postcode);
        $this->assertRegExp(static::TEST_STRING_REGEX, $faker->address);
        $this->assertRegExp(static::TEST_STRING_REGEX, $faker->country);
    }


    
    public function testCompany($locale = null)
    {
        $faker = Faker\Factory::create($locale);

        $this->assertRegExp(static::TEST_STRING_REGEX, $faker->company);
    }


    
    public function testDateTime($locale = null)
    {
        $faker = Faker\Factory::create($locale);

        $this->assertRegExp(static::TEST_STRING_REGEX, $faker->century);
        $this->assertRegExp(static::TEST_STRING_REGEX, $faker->timezone);
    }


    
    public function testInternet($locale = null)
    {
        $faker = Faker\Factory::create($locale);

        $this->assertRegExp(static::TEST_STRING_REGEX, $faker->userName);

        $this->assertRegExp(static::TEST_EMAIL_REGEX, $faker->email);
        $this->assertRegExp(static::TEST_EMAIL_REGEX, $faker->safeEmail);
        $this->assertRegExp(static::TEST_EMAIL_REGEX, $faker->freeEmail);
        $this->assertRegExp(static::TEST_EMAIL_REGEX, $faker->companyEmail);
    }


    
    public function testPerson($locale = null)
    {
        $faker = Faker\Factory::create($locale);

        $this->assertRegExp(static::TEST_STRING_REGEX, $faker->name);
        $this->assertRegExp(static::TEST_STRING_REGEX, $faker->title);
        $this->assertRegExp(static::TEST_STRING_REGEX, $faker->firstName);
        $this->assertRegExp(static::TEST_STRING_REGEX, $faker->lastName);
    }


    
    public function testPhoneNumber($locale = null)
    {
        $faker = Faker\Factory::create($locale);

        $this->assertRegExp(static::TEST_STRING_REGEX, $faker->phoneNumber);
    }


    
    public function testUserAgent($locale = null)
    {
        $faker = Faker\Factory::create($locale);

        $this->assertRegExp(static::TEST_STRING_REGEX, $faker->userAgent);
    }


    
    public function testUuid($locale = null)
    {
        $faker = Faker\Factory::create($locale);

        $this->assertRegExp(static::TEST_STRING_REGEX, $faker->uuid);
    }


    
    public function localeDataProvider()
    {
        $locales = $this->getAllLocales();
        $data = array();

        foreach ($locales as $locale) {
            $data[] = array(
                $locale
            );
        }

        return $data;
    }


    
    private function getAllLocales()
    {
        static $locales = array();

        if ( ! empty($locales)) {
            return $locales;
        }

        // Finding all PHP files in the xx_XX directories
        $providerDir = __DIR__ .'/../../../src/Faker/Provider';
        foreach (glob($providerDir .'/*_*/*.php') as $file) {
            $localisation = basename(dirname($file));

            if (isset($locales[ $localisation ])) {
                continue;
            }

            $locales[ $localisation ] = $localisation;
        }

        return $locales;
    }
}
