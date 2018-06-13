<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\CssSelector\XPath\Extension;

use Symfony\Component\CssSelector\XPath\Translator;
use Symfony\Component\CssSelector\XPath\XPathExpr;


class AttributeMatchingExtension extends AbstractExtension
{
    
    public function getAttributeMatchingTranslators()
    {
        return array(
            'exists' => array($this, 'translateExists'),
            '=' => array($this, 'translateEquals'),
            '~=' => array($this, 'translateIncludes'),
            '|=' => array($this, 'translateDashMatch'),
            '^=' => array($this, 'translatePrefixMatch'),
            '$=' => array($this, 'translateSuffixMatch'),
            '*=' => array($this, 'translateSubstringMatch'),
            '!=' => array($this, 'translateDifferent'),
        );
    }

    
    public function translateExists(XPathExpr $xpath, $attribute, $value)
    {
        return $xpath->addCondition($attribute);
    }

    
    public function translateEquals(XPathExpr $xpath, $attribute, $value)
    {
        return $xpath->addCondition(sprintf('%s = %s', $attribute, Translator::getXpathLiteral($value)));
    }

    
    public function translateIncludes(XPathExpr $xpath, $attribute, $value)
    {
        return $xpath->addCondition($value ? sprintf(
            '%1$s and contains(concat(\' \', normalize-space(%1$s), \' \'), %2$s)',
            $attribute,
            Translator::getXpathLiteral(' '.$value.' ')
        ) : '0');
    }

    
    public function translateDashMatch(XPathExpr $xpath, $attribute, $value)
    {
        return $xpath->addCondition(sprintf(
            '%1$s and (%1$s = %2$s or starts-with(%1$s, %3$s))',
            $attribute,
            Translator::getXpathLiteral($value),
            Translator::getXpathLiteral($value.'-')
        ));
    }

    
    public function translatePrefixMatch(XPathExpr $xpath, $attribute, $value)
    {
        return $xpath->addCondition($value ? sprintf(
            '%1$s and starts-with(%1$s, %2$s)',
            $attribute,
            Translator::getXpathLiteral($value)
        ) : '0');
    }

    
    public function translateSuffixMatch(XPathExpr $xpath, $attribute, $value)
    {
        return $xpath->addCondition($value ? sprintf(
            '%1$s and substring(%1$s, string-length(%1$s)-%2$s) = %3$s',
            $attribute,
            strlen($value) - 1,
            Translator::getXpathLiteral($value)
        ) : '0');
    }

    
    public function translateSubstringMatch(XPathExpr $xpath, $attribute, $value)
    {
        return $xpath->addCondition($value ? sprintf(
            '%1$s and contains(%1$s, %2$s)',
            $attribute,
            Translator::getXpathLiteral($value)
        ) : '0');
    }

    
    public function translateDifferent(XPathExpr $xpath, $attribute, $value)
    {
        return $xpath->addCondition(sprintf(
            $value ? 'not(%1$s) or %1$s != %2$s' : '%s != %s',
            $attribute,
            Translator::getXpathLiteral($value)
        ));
    }

    
    public function getName()
    {
        return 'attribute-matching';
    }
}
