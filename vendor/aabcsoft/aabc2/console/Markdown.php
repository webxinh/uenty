<?php


namespace aabc\console;

use cebe\markdown\block\FencedCodeTrait;
use cebe\markdown\inline\CodeTrait;
use cebe\markdown\inline\EmphStrongTrait;
use cebe\markdown\inline\StrikeoutTrait;
use aabc\helpers\Console;


class Markdown extends \cebe\markdown\Parser
{
    use FencedCodeTrait;
    use CodeTrait;
    use EmphStrongTrait;
    use StrikeoutTrait;

    
    protected $escapeCharacters = [
        '\\', // backslash
        '`', // backtick
        '*', // asterisk
        '_', // underscore
        '~', // tilde
    ];


    
    protected function renderCode($block)
    {
        return Console::ansiFormat($block['content'], [Console::NEGATIVE]) . "\n\n";
    }

    
    protected function renderParagraph($block)
    {
        return rtrim($this->renderAbsy($block['content'])) . "\n\n";
    }

    
    protected function renderInlineCode($element)
    {
        return Console::ansiFormat($element[1], [Console::UNDERLINE]);
    }

    
    protected function renderEmph($element)
    {
        return Console::ansiFormat($this->renderAbsy($element[1]), [Console::ITALIC]);
    }

    
    protected function renderStrong($element)
    {
        return Console::ansiFormat($this->renderAbsy($element[1]), [Console::BOLD]);
    }

    
    protected function renderStrike($element)
    {
        return Console::ansiFormat($this->parseInline($this->renderAbsy($element[1])), [Console::CROSSED_OUT]);
    }
}
