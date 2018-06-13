<?php
/*
 * This file is part of the Text_Template package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Text_Template
{
    
    protected $template = '';

    
    protected $openDelimiter = '{';

    
    protected $closeDelimiter = '}';

    
    protected $values = array();

    
    public function __construct($file = '', $openDelimiter = '{', $closeDelimiter = '}')
    {
        $this->setFile($file);
        $this->openDelimiter  = $openDelimiter;
        $this->closeDelimiter = $closeDelimiter;
    }

    
    public function setFile($file)
    {
        $distFile = $file . '.dist';

        if (file_exists($file)) {
            $this->template = file_get_contents($file);
        }

        else if (file_exists($distFile)) {
            $this->template = file_get_contents($distFile);
        }

        else {
            throw new InvalidArgumentException(
              'Template file could not be loaded.'
            );
        }
    }

    
    public function setVar(array $values, $merge = TRUE)
    {
        if (!$merge || empty($this->values)) {
            $this->values = $values;
        } else {
            $this->values = array_merge($this->values, $values);
        }
    }

    
    public function render()
    {
        $keys = array();

        foreach ($this->values as $key => $value) {
            $keys[] = $this->openDelimiter . $key . $this->closeDelimiter;
        }

        return str_replace($keys, $this->values, $this->template);
    }

    
    public function renderTo($target)
    {
        $fp = @fopen($target, 'wt');

        if ($fp) {
            fwrite($fp, $this->render());
            fclose($fp);
        } else {
            $error = error_get_last();

            throw new RuntimeException(
              sprintf(
                'Could not write to %s: %s',
                $target,
                substr(
                  $error['message'],
                  strpos($error['message'], ':') + 2
                )
              )
            );
        }
    }
}

