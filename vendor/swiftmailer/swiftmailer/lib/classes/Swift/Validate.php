<?php

/*
 * This file is part of SwiftMailer.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Validate
{
    
    private static $grammar = null;

    
    public static function email($email)
    {
        if (self::$grammar === null) {
            self::$grammar = Swift_DependencyContainer::getInstance()
                ->lookup('mime.grammar');
        }

        return (bool) preg_match(
                '/^'.self::$grammar->getDefinition('addr-spec').'$/D',
                $email
            );
    }
}
