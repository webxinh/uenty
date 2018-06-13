<?php
/*
 * This file is part of the php-code-coverage package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianBergmann\CodeCoverage\Report;

use SebastianBergmann\CodeCoverage\CodeCoverage;


class PHP
{
    
    public function process(CodeCoverage $coverage, $target = null)
    {
        $filter = $coverage->filter();

        $output = sprintf(
            '<?php
$coverage = new SebastianBergmann\CodeCoverage\CodeCoverage;
$coverage->setData(%s);
$coverage->setTests(%s);

$filter = $coverage->filter();
$filter->setWhitelistedFiles(%s);

return $coverage;',
            var_export($coverage->getData(true), 1),
            var_export($coverage->getTests(), 1),
            var_export($filter->getWhitelistedFiles(), 1)
        );

        if ($target !== null) {
            return file_put_contents($target, $output);
        } else {
            return $output;
        }
    }
}
