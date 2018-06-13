<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Util_TestDox_ResultPrinter_HTML extends PHPUnit_Util_TestDox_ResultPrinter
{
    
    private $pageHeader = <<<EOT
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <title>Test Documentation</title>
        <style>
            body {
                text-rendering: optimizeLegibility;
                font-variant-ligatures: common-ligatures;
                font-kerning: normal;
                margin-left: 2em;
            }

            body > ul > li {
                font-family: Source Serif Pro, PT Sans, Trebuchet MS, Helvetica, Arial;
                font-size: 2em;
            }

            h2 {
                font-family: Tahoma, Helvetica, Arial;
                font-size: 3em;
            }

            ul {
                list-style: none;
                margin-bottom: 1em;
            }
        </style>
    </head>
    <body>
EOT;

    
    private $classHeader = <<<EOT

        <h2 id="%s">%s</h2>
        <ul>

EOT;

    
    private $classFooter = <<<EOT
        </ul>
EOT;

    
    private $pageFooter = <<<EOT

    </body>
</html>
EOT;

    
    protected function startRun()
    {
        $this->write($this->pageHeader);
    }

    
    protected function startClass($name)
    {
        $this->write(
            sprintf(
                $this->classHeader,
                $name,
                $this->currentTestClassPrettified
            )
        );
    }

    
    protected function onTest($name, $success = true)
    {
        $this->write(
            sprintf(
                "            <li style=\"color: %s;\">%s %s</li>\n",
                $success ? '#555753' : '#ef2929',
                $success ? '✓' : '❌',
                $name
            )
        );
    }

    
    protected function endClass($name)
    {
        $this->write($this->classFooter);
    }

    
    protected function endRun()
    {
        $this->write($this->pageFooter);
    }
}
