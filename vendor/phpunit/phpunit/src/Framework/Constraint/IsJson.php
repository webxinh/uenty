<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_Constraint_IsJson extends PHPUnit_Framework_Constraint
{
    
    protected function matches($other)
    {
        if ($other === '') {
            return false;
        }

        json_decode($other);
        if (json_last_error()) {
            return false;
        }

        return true;
    }

    
    protected function failureDescription($other)
    {
        if ($other === '') {
            return 'an empty string is valid JSON';
        }

        json_decode($other);
        $error = PHPUnit_Framework_Constraint_JsonMatches_ErrorMessageProvider::determineJsonError(
            json_last_error()
        );

        return sprintf(
            '%s is valid JSON (%s)',
            $this->exporter->shortenedExport($other),
            $error
        );
    }

    
    public function toString()
    {
        return 'is valid JSON';
    }
}
