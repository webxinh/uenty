<?php


namespace aabc\helpers;

use aabc\console\Markdown as ConsoleMarkdown;


class BaseConsole
{
    // foreground color control codes
    const FG_BLACK  = 30;
    const FG_RED    = 31;
    const FG_GREEN  = 32;
    const FG_YELLOW = 33;
    const FG_BLUE   = 34;
    const FG_PURPLE = 35;
    const FG_CYAN   = 36;
    const FG_GREY   = 37;
    // background color control codes
    const BG_BLACK  = 40;
    const BG_RED    = 41;
    const BG_GREEN  = 42;
    const BG_YELLOW = 43;
    const BG_BLUE   = 44;
    const BG_PURPLE = 45;
    const BG_CYAN   = 46;
    const BG_GREY   = 47;
    // fonts style control codes
    const RESET       = 0;
    const NORMAL      = 0;
    const BOLD        = 1;
    const ITALIC      = 3;
    const UNDERLINE   = 4;
    const BLINK       = 5;
    const NEGATIVE    = 7;
    const CONCEALED   = 8;
    const CROSSED_OUT = 9;
    const FRAMED      = 51;
    const ENCIRCLED   = 52;
    const OVERLINED   = 53;


    
    public static function moveCursorUp($rows = 1)
    {
        echo "\033[" . (int) $rows . 'A';
    }

    
    public static function moveCursorDown($rows = 1)
    {
        echo "\033[" . (int) $rows . 'B';
    }

    
    public static function moveCursorForward($steps = 1)
    {
        echo "\033[" . (int) $steps . 'C';
    }

    
    public static function moveCursorBackward($steps = 1)
    {
        echo "\033[" . (int) $steps . 'D';
    }

    
    public static function moveCursorNextLine($lines = 1)
    {
        echo "\033[" . (int) $lines . 'E';
    }

    
    public static function moveCursorPrevLine($lines = 1)
    {
        echo "\033[" . (int) $lines . 'F';
    }

    
    public static function moveCursorTo($column, $row = null)
    {
        if ($row === null) {
            echo "\033[" . (int) $column . 'G';
        } else {
            echo "\033[" . (int) $row . ';' . (int) $column . 'H';
        }
    }

    
    public static function scrollUp($lines = 1)
    {
        echo "\033[" . (int) $lines . 'S';
    }

    
    public static function scrollDown($lines = 1)
    {
        echo "\033[" . (int) $lines . 'T';
    }

    
    public static function saveCursorPosition()
    {
        echo "\033[s";
    }

    
    public static function restoreCursorPosition()
    {
        echo "\033[u";
    }

    
    public static function hideCursor()
    {
        echo "\033[?25l";
    }

    
    public static function showCursor()
    {
        echo "\033[?25h";
    }

    
    public static function clearScreen()
    {
        echo "\033[2J";
    }

    
    public static function clearScreenBeforeCursor()
    {
        echo "\033[1J";
    }

    
    public static function clearScreenAfterCursor()
    {
        echo "\033[0J";
    }

    
    public static function clearLine()
    {
        echo "\033[2K";
    }

    
    public static function clearLineBeforeCursor()
    {
        echo "\033[1K";
    }

    
    public static function clearLineAfterCursor()
    {
        echo "\033[0K";
    }

    
    public static function ansiFormatCode($format)
    {
        return "\033[" . implode(';', $format) . 'm';
    }

    
    public static function beginAnsiFormat($format)
    {
        echo "\033[" . implode(';', $format) . 'm';
    }

    
    public static function endAnsiFormat()
    {
        echo "\033[0m";
    }

    
    public static function ansiFormat($string, $format = [])
    {
        $code = implode(';', $format);

        return "\033[0m" . ($code !== '' ? "\033[" . $code . 'm' : '') . $string . "\033[0m";
    }

    
    public static function xtermFgColor($colorCode)
    {
        return '38;5;' . $colorCode;
    }

    
    public static function xtermBgColor($colorCode)
    {
        return '48;5;' . $colorCode;
    }

    
    public static function stripAnsiFormat($string)
    {
        return preg_replace('/\033\[[\d;?]*\w/', '', $string);
    }

    
    public static function ansiStrlen($string)
    {
        return mb_strlen(static::stripAnsiFormat($string));
    }

    
    public static function ansiToHtml($string, $styleMap = [])
    {
        $styleMap = [
            // http://www.w3.org/TR/CSS2/syndata.html#value-def-color
            self::FG_BLACK =>    ['color' => 'black'],
            self::FG_BLUE =>     ['color' => 'blue'],
            self::FG_CYAN =>     ['color' => 'aqua'],
            self::FG_GREEN =>    ['color' => 'lime'],
            self::FG_GREY =>     ['color' => 'silver'],
            // http://meyerweb.com/eric/thoughts/2014/06/19/rebeccapurple/
            // http://dev.w3.org/csswg/css-color/#valuedef-rebeccapurple
            self::FG_PURPLE =>   ['color' => 'rebeccapurple'],
            self::FG_RED =>      ['color' => 'red'],
            self::FG_YELLOW =>   ['color' => 'yellow'],
            self::BG_BLACK =>    ['background-color' => 'black'],
            self::BG_BLUE =>     ['background-color' => 'blue'],
            self::BG_CYAN =>     ['background-color' => 'aqua'],
            self::BG_GREEN =>    ['background-color' => 'lime'],
            self::BG_GREY =>     ['background-color' => 'silver'],
            self::BG_PURPLE =>   ['background-color' => 'rebeccapurple'],
            self::BG_RED =>      ['background-color' => 'red'],
            self::BG_YELLOW =>   ['background-color' => 'yellow'],
            self::BOLD =>        ['font-weight' => 'bold'],
            self::ITALIC =>      ['font-style' => 'italic'],
            self::UNDERLINE =>   ['text-decoration' => ['underline']],
            self::OVERLINED =>   ['text-decoration' => ['overline']],
            self::CROSSED_OUT => ['text-decoration' => ['line-through']],
            self::BLINK =>       ['text-decoration' => ['blink']],
            self::CONCEALED =>   ['visibility' => 'hidden'],
        ] + $styleMap;

        $tags = 0;
        $result = preg_replace_callback(
            '/\033\[([\d;]+)m/',
            function ($ansi) use (&$tags, $styleMap) {
                $style = [];
                $reset = false;
                $negative = false;
                foreach (explode(';', $ansi[1]) as $controlCode) {
                    if ($controlCode == 0) {
                        $style = [];
                        $reset = true;
                    } elseif ($controlCode == self::NEGATIVE) {
                        $negative = true;
                    } elseif (isset($styleMap[$controlCode])) {
                        $style[] = $styleMap[$controlCode];
                    }
                }

                $return = '';
                while ($reset && $tags > 0) {
                    $return .= '</span>';
                    $tags--;
                }
                if (empty($style)) {
                    return $return;
                }

                $currentStyle = [];
                foreach ($style as $content) {
                    $currentStyle = ArrayHelper::merge($currentStyle, $content);
                }

                // if negative is set, invert background and foreground
                if ($negative) {
                    if (isset($currentStyle['color'])) {
                        $fgColor = $currentStyle['color'];
                        unset($currentStyle['color']);
                    }
                    if (isset($currentStyle['background-color'])) {
                        $bgColor = $currentStyle['background-color'];
                        unset($currentStyle['background-color']);
                    }
                    if (isset($fgColor)) {
                        $currentStyle['background-color'] = $fgColor;
                    }
                    if (isset($bgColor)) {
                        $currentStyle['color'] = $bgColor;
                    }
                }

                $styleString = '';
                foreach ($currentStyle as $name => $value) {
                    if (is_array($value)) {
                        $value = implode(' ', $value);
                    }
                    $styleString .= "$name: $value;";
                }
                $tags++;
                return "$return<span style=\"$styleString\">";
            },
            $string
        );
        while ($tags > 0) {
            $result .= '</span>';
            $tags--;
        }
        return $result;
    }

    
    public static function markdownToAnsi($markdown)
    {
        $parser = new ConsoleMarkdown();
        return $parser->parse($markdown);
    }

    
    public static function renderColoredString($string, $colored = true)
    {
        // TODO rework/refactor according to https://github.com/aabcsoft/aabc2/issues/746
        static $conversions = [
            '%y' => [self::FG_YELLOW],
            '%g' => [self::FG_GREEN],
            '%b' => [self::FG_BLUE],
            '%r' => [self::FG_RED],
            '%p' => [self::FG_PURPLE],
            '%m' => [self::FG_PURPLE],
            '%c' => [self::FG_CYAN],
            '%w' => [self::FG_GREY],
            '%k' => [self::FG_BLACK],
            '%n' => [0], // reset
            '%Y' => [self::FG_YELLOW, self::BOLD],
            '%G' => [self::FG_GREEN, self::BOLD],
            '%B' => [self::FG_BLUE, self::BOLD],
            '%R' => [self::FG_RED, self::BOLD],
            '%P' => [self::FG_PURPLE, self::BOLD],
            '%M' => [self::FG_PURPLE, self::BOLD],
            '%C' => [self::FG_CYAN, self::BOLD],
            '%W' => [self::FG_GREY, self::BOLD],
            '%K' => [self::FG_BLACK, self::BOLD],
            '%N' => [0, self::BOLD],
            '%3' => [self::BG_YELLOW],
            '%2' => [self::BG_GREEN],
            '%4' => [self::BG_BLUE],
            '%1' => [self::BG_RED],
            '%5' => [self::BG_PURPLE],
            '%6' => [self::BG_CYAN],
            '%7' => [self::BG_GREY],
            '%0' => [self::BG_BLACK],
            '%F' => [self::BLINK],
            '%U' => [self::UNDERLINE],
            '%8' => [self::NEGATIVE],
            '%9' => [self::BOLD],
            '%_' => [self::BOLD],
        ];

        if ($colored) {
            $string = str_replace('%%', '% ', $string);
            foreach ($conversions as $key => $value) {
                $string = str_replace(
                    $key,
                    static::ansiFormatCode($value),
                    $string
                );
            }
            $string = str_replace('% ', '%', $string);
        } else {
            $string = preg_replace('/%((%)|.)/', '$2', $string);
        }

        return $string;
    }

    
    public static function escape($string)
    {
        // TODO rework/refactor according to https://github.com/aabcsoft/aabc2/issues/746
        return str_replace('%', '%%', $string);
    }

    
    public static function streamSupportsAnsiColors($stream)
    {
        return DIRECTORY_SEPARATOR === '\\'
            ? getenv('ANSICON') !== false || getenv('ConEmuANSI') === 'ON'
            : function_exists('posix_isatty') && @posix_isatty($stream);
    }

    
    public static function isRunningOnWindows()
    {
        return DIRECTORY_SEPARATOR === '\\';
    }

    
    public static function getScreenSize($refresh = false)
    {
        static $size;
        if ($size !== null && !$refresh) {
            return $size;
        }

        if (static::isRunningOnWindows()) {
            $output = [];
            exec('mode con', $output);
            if (isset($output, $output[1]) && strpos($output[1], 'CON') !== false) {
                return $size = [(int) preg_replace('~\D~', '', $output[4]), (int) preg_replace('~\D~', '', $output[3])];
            }
        } else {
            // try stty if available
            $stty = [];
            if (exec('stty -a 2>&1', $stty)) {
                $stty = implode(' ', $stty);

                // Linux stty output
                if (preg_match('/rows\s+(\d+);\s*columns\s+(\d+);/mi', $stty, $matches)) {
                    return $size = [(int)$matches[2], (int)$matches[1]];
                }

                // MacOS stty output
                if (preg_match('/(\d+)\s+rows;\s*(\d+)\s+columns;/mi', $stty, $matches)) {
                    return $size = [(int)$matches[2], (int)$matches[1]];
                }
            }

            // fallback to tput, which may not be updated on terminal resize
            if (($width = (int) exec('tput cols 2>&1')) > 0 && ($height = (int) exec('tput lines 2>&1')) > 0) {
                return $size = [$width, $height];
            }

            // fallback to ENV variables, which may not be updated on terminal resize
            if (($width = (int) getenv('COLUMNS')) > 0 && ($height = (int) getenv('LINES')) > 0) {
                return $size = [$width, $height];
            }
        }

        return $size = false;
    }

    
    public static function wrapText($text, $indent = 0, $refresh = false)
    {
        $size = static::getScreenSize($refresh);
        if ($size === false || $size[0] <= $indent) {
            return $text;
        }
        $pad = str_repeat(' ', $indent);
        $lines = explode("\n", wordwrap($text, $size[0] - $indent, "\n", true));
        $first = true;
        foreach ($lines as $i => $line) {
            if ($first) {
                $first = false;
                continue;
            }
            $lines[$i] = $pad . $line;
        }
        return implode("\n", $lines);
    }

    
    public static function stdin($raw = false)
    {
        return $raw ? fgets(\STDIN) : rtrim(fgets(\STDIN), PHP_EOL);
    }

    
    public static function stdout($string)
    {
        return fwrite(\STDOUT, $string);
    }

    
    public static function stderr($string)
    {
        return fwrite(\STDERR, $string);
    }

    
    public static function input($prompt = null)
    {
        if (isset($prompt)) {
            static::stdout($prompt);
        }

        return static::stdin();
    }

    
    public static function output($string = null)
    {
        return static::stdout($string . PHP_EOL);
    }

    
    public static function error($string = null)
    {
        return static::stderr($string . PHP_EOL);
    }

    
    public static function prompt($text, $options = [])
    {
        $options = ArrayHelper::merge(
            [
                'required'  => false,
                'default'   => null,
                'pattern'   => null,
                'validator' => null,
                'error'     => 'Invalid input.',
            ],
            $options
        );
        $error   = null;

        top:
        $input = $options['default']
            ? static::input("$text [" . $options['default'] . '] ')
            : static::input("$text ");

        if ($input === '') {
            if (isset($options['default'])) {
                $input = $options['default'];
            } elseif ($options['required']) {
                static::output($options['error']);
                goto top;
            }
        } elseif ($options['pattern'] && !preg_match($options['pattern'], $input)) {
            static::output($options['error']);
            goto top;
        } elseif ($options['validator'] &&
            !call_user_func_array($options['validator'], [$input, &$error])
        ) {
            static::output(isset($error) ? $error : $options['error']);
            goto top;
        }

        return $input;
    }

    
    public static function confirm($message, $default = false)
    {
        while (true) {
            static::stdout($message . ' (yes|no) [' . ($default ? 'yes' : 'no') . ']:');
            $input = trim(static::stdin());

            if (empty($input)) {
                return $default;
            }

            if (!strcasecmp($input, 'y') || !strcasecmp($input, 'yes')) {
                return true;
            }

            if (!strcasecmp($input, 'n') || !strcasecmp($input, 'no')) {
                return false;
            }
        }
    }

    
    public static function select($prompt, $options = [])
    {
        top:
        static::stdout("$prompt [" . implode(',', array_keys($options)) . ',?]: ');
        $input = static::stdin();
        if ($input === '?') {
            foreach ($options as $key => $value) {
                static::output(" $key - $value");
            }
            static::output(' ? - Show help');
            goto top;
        } elseif (!array_key_exists($input, $options)) {
            goto top;
        }

        return $input;
    }

    private static $_progressStart;
    private static $_progressWidth;
    private static $_progressPrefix;
    private static $_progressEta;
    private static $_progressEtaLastDone = 0;
    private static $_progressEtaLastUpdate;

    
    public static function startProgress($done, $total, $prefix = '', $width = null)
    {
        self::$_progressStart = time();
        self::$_progressWidth = $width;
        self::$_progressPrefix = $prefix;
        self::$_progressEta = null;
        self::$_progressEtaLastDone = 0;
        self::$_progressEtaLastUpdate = time();

        static::updateProgress($done, $total);
    }

    
    public static function updateProgress($done, $total, $prefix = null)
    {
        $width = self::$_progressWidth;
        if ($width === false) {
            $width = 0;
        } else {
            $screenSize = static::getScreenSize(true);
            if ($screenSize === false && $width < 1) {
                $width = 0;
            } elseif ($width === null) {
                $width = $screenSize[0];
            } elseif ($width > 0 && $width < 1) {
                $width = floor($screenSize[0] * $width);
            }
        }
        if ($prefix === null) {
            $prefix = self::$_progressPrefix;
        } else {
            self::$_progressPrefix = $prefix;
        }
        $width -= static::ansiStrlen($prefix);

        $percent = ($total == 0) ? 1 : $done / $total;
        $info = sprintf('%d%% (%d/%d)', $percent * 100, $done, $total);

        if ($done > $total || $done == 0) {
            self::$_progressEta = null;
            self::$_progressEtaLastUpdate = time();
        } elseif ($done < $total) {
            // update ETA once per second to avoid flapping
            if (time() - self::$_progressEtaLastUpdate > 1 && $done > self::$_progressEtaLastDone) {
                $rate = (time() - (self::$_progressEtaLastUpdate ?: self::$_progressStart)) / ($done - self::$_progressEtaLastDone);
                self::$_progressEta = $rate * ($total - $done);
                self::$_progressEtaLastUpdate = time();
                self::$_progressEtaLastDone = $done;
            }
        }
        if (self::$_progressEta === null) {
            $info .= ' ETA: n/a';
        } else {
            $info .= sprintf(' ETA: %d sec.', self::$_progressEta);
        }

        // Number extra characters outputted. These are opening [, closing ], and space before info
        // Since Windows uses \r\n\ for line endings, there's one more in the case
        $extraChars = static::isRunningOnWindows() ? 4 : 3;
        $width -= $extraChars + static::ansiStrlen($info);
        // skipping progress bar on very small display or if forced to skip
        if ($width < 5) {
            static::stdout("\r$prefix$info   ");
        } else {
            if ($percent < 0) {
                $percent = 0;
            } elseif ($percent > 1) {
                $percent = 1;
            }
            $bar = floor($percent * $width);
            $status = str_repeat('=', $bar);
            if ($bar < $width) {
                $status .= '>';
                $status .= str_repeat(' ', $width - $bar - 1);
            }
            static::stdout("\r$prefix" . "[$status] $info");
        }
        flush();
    }

    
    public static function endProgress($remove = false, $keepPrefix = true)
    {
        if ($remove === false) {
            static::stdout(PHP_EOL);
        } else {
            if (static::streamSupportsAnsiColors(STDOUT)) {
                static::clearLine();
            }
            static::stdout("\r" . ($keepPrefix ? self::$_progressPrefix : '') . (is_string($remove) ? $remove : ''));
        }
        flush();

        self::$_progressStart = null;
        self::$_progressWidth = null;
        self::$_progressPrefix = '';
        self::$_progressEta = null;
        self::$_progressEtaLastDone = 0;
        self::$_progressEtaLastUpdate = null;
    }
}
