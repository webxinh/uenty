<?php
/*
 * This file is part of the Diff package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianBergmann\Diff;


class Diff
{
    
    private $from;

    
    private $to;

    
    private $chunks;

    
    public function __construct($from, $to, array $chunks = array())
    {
        $this->from   = $from;
        $this->to     = $to;
        $this->chunks = $chunks;
    }

    
    public function getFrom()
    {
        return $this->from;
    }

    
    public function getTo()
    {
        return $this->to;
    }

    
    public function getChunks()
    {
        return $this->chunks;
    }

    
    public function setChunks(array $chunks)
    {
        $this->chunks = $chunks;
    }
}
