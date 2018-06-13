<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Yaml;


class Dumper
{
    
    protected $indentation;

    
    public function __construct($indentation = 4)
    {
        if ($indentation < 1) {
            throw new \InvalidArgumentException('The indentation must be greater than zero.');
        }

        $this->indentation = $indentation;
    }

    
    public function setIndentation($num)
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 3.1 and will be removed in 4.0. Pass the indentation to the constructor instead.', E_USER_DEPRECATED);

        $this->indentation = (int) $num;
    }

    
    public function dump($input, $inline = 0, $indent = 0, $flags = 0)
    {
        if (is_bool($flags)) {
            @trigger_error('Passing a boolean flag to toggle exception handling is deprecated since version 3.1 and will be removed in 4.0. Use the Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE flag instead.', E_USER_DEPRECATED);

            if ($flags) {
                $flags = Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE;
            } else {
                $flags = 0;
            }
        }

        if (func_num_args() >= 5) {
            @trigger_error('Passing a boolean flag to toggle object support is deprecated since version 3.1 and will be removed in 4.0. Use the Yaml::DUMP_OBJECT flag instead.', E_USER_DEPRECATED);

            if (func_get_arg(4)) {
                $flags |= Yaml::DUMP_OBJECT;
            }
        }

        $output = '';
        $prefix = $indent ? str_repeat(' ', $indent) : '';

        if ($inline <= 0 || !is_array($input) || empty($input)) {
            $output .= $prefix.Inline::dump($input, $flags);
        } else {
            $isAHash = Inline::isHash($input);

            foreach ($input as $key => $value) {
                if ($inline >= 1 && Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK & $flags && is_string($value) && false !== strpos($value, "\n")) {
                    $output .= sprintf("%s%s%s |\n", $prefix, $isAHash ? Inline::dump($key, $flags).':' : '-', '');

                    foreach (preg_split('/\n|\r\n/', $value) as $row) {
                        $output .= sprintf("%s%s%s\n", $prefix, str_repeat(' ', $this->indentation), $row);
                    }

                    continue;
                }

                $willBeInlined = $inline - 1 <= 0 || !is_array($value) || empty($value);

                $output .= sprintf('%s%s%s%s',
                    $prefix,
                    $isAHash ? Inline::dump($key, $flags).':' : '-',
                    $willBeInlined ? ' ' : "\n",
                    $this->dump($value, $inline - 1, $willBeInlined ? 0 : $indent + $this->indentation, $flags)
                ).($willBeInlined ? "\n" : '');
            }
        }

        return $output;
    }
}
