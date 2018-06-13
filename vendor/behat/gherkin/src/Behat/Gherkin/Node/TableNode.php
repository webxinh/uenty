<?php

/*
 * This file is part of the Behat Gherkin.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Gherkin\Node;

use ArrayIterator;
use Behat\Gherkin\Exception\NodeException;
use Iterator;
use IteratorAggregate;


class TableNode implements ArgumentInterface, IteratorAggregate
{
    
    private $table;
    
    private $maxLineLength = array();

    
    public function __construct(array $table)
    {
        $this->table = $table;
        $columnCount = null;

        foreach ($this->getRows() as $row) {
            if ($columnCount === null) {
                $columnCount = count($row);
            }

            if (count($row) !== $columnCount) {
                throw new NodeException('Table does not have same number of columns in every row.');
            }

            foreach ($row as $column => $string) {
                if (!isset($this->maxLineLength[$column])) {
                    $this->maxLineLength[$column] = 0;
                }

                $this->maxLineLength[$column] = max($this->maxLineLength[$column], mb_strlen($string, 'utf8'));
            }
        }
    }

    
    public function getNodeType()
    {
        return 'Table';
    }

    
    public function getHash()
    {
        return $this->getColumnsHash();
    }

    
    public function getColumnsHash()
    {
        $rows = $this->getRows();
        $keys = array_shift($rows);

        $hash = array();
        foreach ($rows as $row) {
            $hash[] = array_combine($keys, $row);
        }

        return $hash;
    }

    
    public function getRowsHash()
    {
        $hash = array();

        foreach ($this->getRows() as $row) {
            $hash[array_shift($row)] = (1 == count($row)) ? $row[0] : $row;
        }

        return $hash;
    }

    
    public function getTable()
    {
        return $this->table;
    }

    
    public function getRows()
    {
        return array_values($this->table);
    }

    
    public function getLines()
    {
        return array_keys($this->table);
    }

    
    public function getRow($index)
    {
        $rows = $this->getRows();

        if (!isset($rows[$index])) {
            throw new NodeException(sprintf('Rows #%d does not exist in table.', $index));
        }

        return $rows[$index];
    }

    
    public function getColumn($index)
    {
        if ($index >= count($this->getRow(0))) {
            throw new NodeException(sprintf('Column #%d does not exist in table.', $index));
        }

        $rows = $this->getRows();
        $column = array();

        foreach ($rows as $row) {
            $column[] = $row[$index];
        }

        return $column;
    }

    
    public function getRowLine($index)
    {
        $lines = array_keys($this->table);

        if (!isset($lines[$index])) {
            throw new NodeException(sprintf('Rows #%d does not exist in table.', $index));
        }

        return $lines[$index];
    }

    
    public function getRowAsString($rowNum)
    {
        $values = array();
        foreach ($this->getRow($rowNum) as $column => $value) {
            $values[] = $this->padRight(' ' . $value . ' ', $this->maxLineLength[$column] + 2);
        }

        return sprintf('|%s|', implode('|', $values));
    }

    
    public function getRowAsStringWithWrappedValues($rowNum, $wrapper)
    {
        $values = array();
        foreach ($this->getRow($rowNum) as $column => $value) {
            $value = $this->padRight(' ' . $value . ' ', $this->maxLineLength[$column] + 2);

            $values[] = call_user_func($wrapper, $value, $column);
        }

        return sprintf('|%s|', implode('|', $values));
    }

    
    public function getTableAsString()
    {
        $lines = array();
        for ($i = 0; $i < count($this->getRows()); $i++) {
            $lines[] = $this->getRowAsString($i);
        }

        return implode("\n", $lines);
    }

    
    public function getLine()
    {
        return $this->getRowLine(0);
    }

    
    public function __toString()
    {
        return $this->getTableAsString();
    }

    
    public function getIterator()
    {
        return new ArrayIterator($this->getHash());
    }

    
    protected function padRight($text, $length)
    {
        while ($length > mb_strlen($text, 'utf8')) {
            $text = $text . ' ';
        }

        return $text;
    }
}
