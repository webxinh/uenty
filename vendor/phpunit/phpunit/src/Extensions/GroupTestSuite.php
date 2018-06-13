<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Extensions_GroupTestSuite extends PHPUnit_Framework_TestSuite
{
    public function __construct(PHPUnit_Framework_TestSuite $suite, array $groups)
    {
        $groupSuites = [];
        $name        = $suite->getName();

        foreach ($groups as $group) {
            $groupSuites[$group] = new PHPUnit_Framework_TestSuite($name . ' - ' . $group);
            $this->addTest($groupSuites[$group]);
        }

        $tests = new RecursiveIteratorIterator(
            new PHPUnit_Util_TestSuiteIterator($suite),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($tests as $test) {
            if ($test instanceof PHPUnit_Framework_TestCase) {
                $testGroups = PHPUnit_Util_Test::getGroups(
                    get_class($test),
                    $test->getName(false)
                );

                foreach ($groups as $group) {
                    foreach ($testGroups as $testGroup) {
                        if ($group == $testGroup) {
                            $groupSuites[$group]->addTest($test);
                        }
                    }
                }
            }
        }
    }
}
