<?php

/*
 * This file is part of the Behat Gherkin.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Gherkin\Node;


class ExampleTableNode extends TableNode
{
    
    private $keyword;

    
    public function __construct(array $table, $keyword)
    {
        $this->keyword = $keyword;

        parent::__construct($table);
    }

    
    public function getNodeType()
    {
        return 'ExampleTable';
    }

    
    public function getKeyword()
    {
        return $this->keyword;
    }
}
