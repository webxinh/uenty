<?php


namespace aabc\i18n;

use Aabc;


class GettextPoFile extends GettextFile
{
    
    public function load($filePath, $context)
    {
        $pattern = '/(msgctxt\s+"(.*?(?<!\\\\))")?\s+' // context
            . 'msgid\s+((?:".*(?<!\\\\)"\s*)+)\s+' // message ID, i.e. original string
            . 'msgstr\s+((?:".*(?<!\\\\)"\s*)+)/'; // translated string
        $content = file_get_contents($filePath);
        $matches = [];
        $matchCount = preg_match_all($pattern, $content, $matches);

        $messages = [];
        for ($i = 0; $i < $matchCount; ++$i) {
            if ($matches[2][$i] === $context) {
                $id = $this->decode($matches[3][$i]);
                $message = $this->decode($matches[4][$i]);
                $messages[$id] = $message;
            }
        }

        return $messages;
    }

    
    public function save($filePath, $messages)
    {
        $language = str_replace('-', '_', basename(dirname($filePath)));
        $headers = [
            'msgid ""',
            'msgstr ""',
            '"Project-Id-Version: \n"',
            '"POT-Creation-Date: \n"',
            '"PO-Revision-Date: \n"',
            '"Last-Translator: \n"',
            '"Language-Team: \n"',
            '"Language: ' . $language . '\n"',
            '"MIME-Version: 1.0\n"',
            '"Content-Type: text/plain; charset=' . Aabc::$app->charset . '\n"',
            '"Content-Transfer-Encoding: 8bit\n"'
        ];
        $content = implode("\n", $headers) . "\n\n";
        foreach ($messages as $id => $message) {
            $separatorPosition = strpos($id, chr(4));
            if ($separatorPosition !== false) {
                $content .= 'msgctxt "' . substr($id, 0, $separatorPosition) . "\"\n";
                $id = substr($id, $separatorPosition + 1);
            }
            $content .= 'msgid "' . $this->encode($id) . "\"\n";
            $content .= 'msgstr "' . $this->encode($message) . "\"\n\n";
        }
        file_put_contents($filePath, $content);
    }

    
    protected function encode($string)
    {
        return str_replace(
            ['"', "\n", "\t", "\r"],
            ['\\"', '\\n', '\\t', '\\r'],
            $string
        );
    }

    
    protected function decode($string)
    {
        $string = preg_replace(
            ['/"\s+"/', '/\\\\n/', '/\\\\r/', '/\\\\t/', '/\\\\"/'],
            ['', "\n", "\r", "\t", '"'],
            $string
        );

        return substr(rtrim($string), 1, -1);
    }
}
