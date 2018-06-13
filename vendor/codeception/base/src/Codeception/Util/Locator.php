<?php
namespace Codeception\Util;

use Symfony\Component\CssSelector\CssSelectorConverter;
use Symfony\Component\CssSelector\Exception\ParseException;
use Symfony\Component\CssSelector\XPath\Translator;


class Locator
{
    
    public static function combine($selector1, $selector2)
    {
        $selectors = func_get_args();
        foreach ($selectors as $k => $v) {
            $selectors[$k] = self::toXPath($v);
            if (!$selectors[$k]) {
                throw new \Exception("$v is invalid CSS or XPath");
            }
        }
        return implode(' | ', $selectors);
    }

    
    public static function href($url)
    {
        return sprintf('//a[@href=normalize-space(%s)]', Translator::getXpathLiteral($url));
    }

    
    public static function tabIndex($index)
    {
        return sprintf('//*[@tabindex = normalize-space(%d)]', $index);
    }

    
    public static function option($value)
    {
        return sprintf('//option[.=normalize-space("%s")]', $value);
    }

    protected static function toXPath($selector)
    {
        try {
            $xpath = (new CssSelectorConverter())->toXPath($selector);
            return $xpath;
        } catch (ParseException $e) {
            if (self::isXPath($selector)) {
                return $selector;
            }
        }
        return null;
    }

    
    public static function find($element, array $attributes)
    {
        $operands = [];
        foreach ($attributes as $attribute => $value) {
            if (is_int($attribute)) {
                $operands[] = '@' . $value;
            } else {
                $operands[] = '@' . $attribute . ' = ' . Translator::getXpathLiteral($value);
            }
        }
        return sprintf('//%s[%s]', $element, implode(' and ', $operands));
    }

    
    public static function isCSS($selector)
    {
        try {
            (new CssSelectorConverter())->toXPath($selector);
        } catch (ParseException $e) {
            return false;
        }
        return true;
    }

    
    public static function isXPath($locator)
    {
        $document = new \DOMDocument('1.0', 'UTF-8');
        $xpath = new \DOMXPath($document);
        return @$xpath->evaluate($locator, $document) !== false;
    }

    
    public static function isID($id)
    {
        return (bool)preg_match('~^#[\w\.\-\[\]\=\^\~\:]+$~', $id);
    }

    
    public static function isClass($class)
    {
        return (bool)preg_match('~^\.[\w\.\-\[\]\=\^\~\:]+$~', $class);
    }

    
    public static function contains($element, $text)
    {
        $text = Translator::getXpathLiteral($text);
        return sprintf('%s[%s]', self::toXPath($element), "contains(., $text)");
    }

    
    public static function elementAt($element, $position)
    {
        if (is_int($position) && $position < 0) {
            $position++; // -1 points to the last element
            $position = 'last()-'.abs($position);
        }
        if ($position === 0) {
            throw new \InvalidArgumentException(
                '0 is not valid element position. XPath expects first element to have index 1'
            );
        }
        return sprintf('(%s)[position()=%s]', self::toXPath($element), $position);
    }

    
    public static function firstElement($element)
    {
        return self::elementAt($element, 1);
    }

    
    public static function lastElement($element)
    {
        return self::elementAt($element, 'last()');
    }

    
    public static function humanReadableString($selector)
    {
        if (is_string($selector)) {
            return "'$selector'";
        }
        if (is_array($selector)) {
            $type = strtolower(key($selector));
            $locator = $selector[$type];
            return "$type '$locator'";
        }
        if (class_exists('\Facebook\WebDriver\WebDriverBy')) {
            if ($selector instanceof \Facebook\WebDriver\WebDriverBy) {
                $type = $selector->getMechanism();
                $locator = $selector->getValue();
                return "$type '$locator'";
            }
        }
        throw new \InvalidArgumentException("Unrecognized selector");
    }
}
