<?php


namespace aabc\helpers;

use Aabc;
use aabc\base\InvalidParamException;


class BaseMarkdown
{
    
    public static $flavors = [
        'original' => [
            'class' => 'cebe\markdown\Markdown',
            'html5' => true,
        ],
        'gfm' => [
            'class' => 'cebe\markdown\GithubMarkdown',
            'html5' => true,
        ],
        'gfm-comment' => [
            'class' => 'cebe\markdown\GithubMarkdown',
            'html5' => true,
            'enableNewlines' => true,
        ],
        'extra' => [
            'class' => 'cebe\markdown\MarkdownExtra',
            'html5' => true,
        ],
    ];
    
    public static $defaultFlavor = 'original';


    
    public static function process($markdown, $flavor = null)
    {
        $parser = static::getParser($flavor);

        return $parser->parse($markdown);
    }

    
    public static function processParagraph($markdown, $flavor = null)
    {
        $parser = static::getParser($flavor);

        return $parser->parseParagraph($markdown);
    }

    
    protected static function getParser($flavor)
    {
        if ($flavor === null) {
            $flavor = static::$defaultFlavor;
        }
        /* @var $parser \cebe\markdown\Markdown */
        if (!isset(static::$flavors[$flavor])) {
            throw new InvalidParamException("Markdown flavor '$flavor' is not defined.'");
        } elseif (!is_object($config = static::$flavors[$flavor])) {
            static::$flavors[$flavor] = Aabc::createObject($config);
        }

        return static::$flavors[$flavor];
    }
}
