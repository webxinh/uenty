<?php


namespace aabc\console\controllers;

use Aabc;
use aabc\console\Controller;
use aabc\console\Exception;
use aabc\helpers\Console;
use aabc\helpers\FileHelper;
use aabc\helpers\VarDumper;
use aabc\i18n\GettextPoFile;


class MessageController extends Controller
{
    
    public $defaultAction = 'extract';
    
    public $sourcePath = '@aabc';
    
    public $messagePath = '@aabc/messages';
    
    public $languages = [];
    
    public $translator = 'Aabc::t';
    
    public $sort = false;
    
    public $overwrite = true;
    
    public $removeUnused = false;
    
    public $markUnused = true;
    
    public $except = [
        '.svn',
        '.git',
        '.gitignore',
        '.gitkeep',
        '.hgignore',
        '.hgkeep',
        '/messages',
        '/BaseAabc.php', // contains examples about Aabc:t()
    ];
    
    public $only = ['*.php'];
    
    public $format = 'php';
    
    public $db = 'db';
    
    public $sourceMessageTable = '{{%source_message}}';
    
    public $messageTable = '{{%message}}';
    
    public $catalog = 'messages';
    
    public $ignoreCategories = [];


    
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'sourcePath',
            'messagePath',
            'languages',
            'translator',
            'sort',
            'overwrite',
            'removeUnused',
            'markUnused',
            'except',
            'only',
            'format',
            'db',
            'sourceMessageTable',
            'messageTable',
            'catalog',
            'ignoreCategories',
        ]);
    }

    
    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), [
            'c' => 'catalog',
            'e' => 'except',
            'f' => 'format',
            'i' => 'ignoreCategories',
            'l' => 'languages',
            'u' => 'markUnused',
            'p' => 'messagePath',
            'o' => 'only',
            'w' => 'overwrite',
            'S' => 'sort',
            't' => 'translator',
            'm' => 'sourceMessageTable',
            's' => 'sourcePath',
            'r' => 'removeUnused',
        ]);
    }

    
    public function actionConfig($filePath)
    {
        $filePath = Aabc::getAlias($filePath);
        if (file_exists($filePath)) {
            if (!$this->confirm("File '{$filePath}' already exists. Do you wish to overwrite it?")) {
                return self::EXIT_CODE_NORMAL;
            }
        }

        $array = VarDumper::export($this->getOptionValues($this->action->id));
        $content = <<<EOD
<?php

return $array;

EOD;

        if (file_put_contents($filePath, $content) !== false) {
            $this->stdout("Configuration file created: '{$filePath}'.\n\n", Console::FG_GREEN);
            return self::EXIT_CODE_NORMAL;
        } else {
            $this->stdout("Configuration file was NOT created: '{$filePath}'.\n\n", Console::FG_RED);
            return self::EXIT_CODE_ERROR;
        }
    }

    
    public function actionConfigTemplate($filePath)
    {
        $filePath = Aabc::getAlias($filePath);
        if (file_exists($filePath)) {
            if (!$this->confirm("File '{$filePath}' already exists. Do you wish to overwrite it?")) {
                return self::EXIT_CODE_NORMAL;
            }
        }
        if (copy(Aabc::getAlias('@aabc/views/messageConfig.php'), $filePath)) {
            $this->stdout("Configuration file template created at '{$filePath}'.\n\n", Console::FG_GREEN);
            return self::EXIT_CODE_NORMAL;
        } else {
            $this->stdout("Configuration file template was NOT created at '{$filePath}'.\n\n", Console::FG_RED);
            return self::EXIT_CODE_ERROR;
        }
    }

    
    public function actionExtract($configFile = null)
    {
        $configFileContent = [];
        if ($configFile !== null) {
            $configFile = Aabc::getAlias($configFile);
            if (!is_file($configFile)) {
                throw new Exception("The configuration file does not exist: $configFile");
            } else {
                $configFileContent = require($configFile);
            }
        }

        $config = array_merge(
            $this->getOptionValues($this->action->id),
            $configFileContent,
            $this->getPassedOptionValues()
        );
        $config['sourcePath'] = Aabc::getAlias($config['sourcePath']);
        $config['messagePath'] = Aabc::getAlias($config['messagePath']);

        if (!isset($config['sourcePath'], $config['languages'])) {
            throw new Exception('The configuration file must specify "sourcePath" and "languages".');
        }
        if (!is_dir($config['sourcePath'])) {
            throw new Exception("The source path {$config['sourcePath']} is not a valid directory.");
        }
        if (empty($config['format']) || !in_array($config['format'], ['php', 'po', 'pot', 'db'])) {
            throw new Exception('Format should be either "php", "po", "pot" or "db".');
        }
        if (in_array($config['format'], ['php', 'po', 'pot'])) {
            if (!isset($config['messagePath'])) {
                throw new Exception('The configuration file must specify "messagePath".');
            } elseif (!is_dir($config['messagePath'])) {
                throw new Exception("The message path {$config['messagePath']} is not a valid directory.");
            }
        }
        if (empty($config['languages'])) {
            throw new Exception('Languages cannot be empty.');
        }

        $files = FileHelper::findFiles(realpath($config['sourcePath']), $config);

        $messages = [];
        foreach ($files as $file) {
            $messages = array_merge_recursive($messages, $this->extractMessages($file, $config['translator'], $config['ignoreCategories']));
        }
        if (in_array($config['format'], ['php', 'po'])) {
            foreach ($config['languages'] as $language) {
                $dir = $config['messagePath'] . DIRECTORY_SEPARATOR . $language;
                if (!is_dir($dir)) {
                    @mkdir($dir);
                }
                if ($config['format'] === 'po') {
                    $catalog = isset($config['catalog']) ? $config['catalog'] : 'messages';
                    $this->saveMessagesToPO($messages, $dir, $config['overwrite'], $config['removeUnused'], $config['sort'], $catalog, $config['markUnused']);
                } else {
                    $this->saveMessagesToPHP($messages, $dir, $config['overwrite'], $config['removeUnused'], $config['sort'], $config['markUnused']);
                }
            }
        } elseif ($config['format'] === 'db') {
            $db = \Aabc::$app->get(isset($config['db']) ? $config['db'] : 'db');
            if (!$db instanceof \aabc\db\Connection) {
                throw new Exception('The "db" option must refer to a valid database application component.');
            }
            $sourceMessageTable = isset($config['sourceMessageTable']) ? $config['sourceMessageTable'] : '{{%source_message}}';
            $messageTable = isset($config['messageTable']) ? $config['messageTable'] : '{{%message}}';
            $this->saveMessagesToDb(
                $messages,
                $db,
                $sourceMessageTable,
                $messageTable,
                $config['removeUnused'],
                $config['languages'],
                $config['markUnused']
            );
        } elseif ($config['format'] === 'pot') {
            $catalog = isset($config['catalog']) ? $config['catalog'] : 'messages';
            $this->saveMessagesToPOT($messages, $config['messagePath'], $catalog);
        }
    }

    
    protected function saveMessagesToDb($messages, $db, $sourceMessageTable, $messageTable, $removeUnused, $languages, $markUnused)
    {
        $q = new \aabc\db\Query;
        $current = [];

        foreach ($q->select(['id', 'category', 'message'])->from($sourceMessageTable)->all($db) as $row) {
            $current[$row['category']][$row['id']] = $row['message'];
        }

        $new = [];
        $obsolete = [];

        foreach ($messages as $category => $msgs) {
            $msgs = array_unique($msgs);

            if (isset($current[$category])) {
                $new[$category] = array_diff($msgs, $current[$category]);
                $obsolete += array_diff($current[$category], $msgs);
            } else {
                $new[$category] = $msgs;
            }
        }

        foreach (array_diff(array_keys($current), array_keys($messages)) as $category) {
            $obsolete += $current[$category];
        }

        if (!$removeUnused) {
            foreach ($obsolete as $pk => $m) {
                if (mb_substr($m, 0, 2) === '@@' && mb_substr($m, -2) === '@@') {
                    unset($obsolete[$pk]);
                }
            }
        }

        $obsolete = array_keys($obsolete);
        $this->stdout('Inserting new messages...');
        $savedFlag = false;

        foreach ($new as $category => $msgs) {
            foreach ($msgs as $m) {
                $savedFlag = true;
                $lastPk = $db->schema->insert($sourceMessageTable, ['category' => $category, 'message' => $m]);
                foreach ($languages as $language) {
                    $db->createCommand()
                       ->insert($messageTable, ['id' => $lastPk['id'], 'language' => $language])
                       ->execute();
                }
            }
        }

        $this->stdout($savedFlag ? "saved.\n" : "Nothing new...skipped.\n");
        $this->stdout($removeUnused ? 'Deleting obsoleted messages...' : 'Updating obsoleted messages...');

        if (empty($obsolete)) {
            $this->stdout("Nothing obsoleted...skipped.\n");
        } else {
            if ($removeUnused) {
                $db->createCommand()
                   ->delete($sourceMessageTable, ['in', 'id', $obsolete])
                   ->execute();
                $this->stdout("deleted.\n");
            } elseif ($markUnused) {
                $db->createCommand()
                   ->update(
                       $sourceMessageTable,
                       ['message' => new \aabc\db\Expression("CONCAT('@@',message,'@@')")],
                       ['in', 'id', $obsolete]
                   )->execute();
                $this->stdout("updated.\n");
            } else {
                $this->stdout("kept untouched.\n");
            }
        }
    }

    
    protected function extractMessages($fileName, $translator, $ignoreCategories = [])
    {
        $coloredFileName = Console::ansiFormat($fileName, [Console::FG_CYAN]);
        $this->stdout("Extracting messages from $coloredFileName...\n");

        $subject = file_get_contents($fileName);
        $messages = [];
        $tokens = token_get_all($subject);
        foreach ((array) $translator as $currentTranslator) {
            $translatorTokens = token_get_all('<?php ' . $currentTranslator);
            array_shift($translatorTokens);
            $messages = array_merge_recursive($messages, $this->extractMessagesFromTokens($tokens, $translatorTokens, $ignoreCategories));
        }

        $this->stdout("\n");

        return $messages;
    }

    
    private function extractMessagesFromTokens(array $tokens, array $translatorTokens, array $ignoreCategories)
    {
        $messages = [];
        $translatorTokensCount = count($translatorTokens);
        $matchedTokensCount = 0;
        $buffer = [];
        $pendingParenthesisCount = 0;

        foreach ($tokens as $token) {
            // finding out translator call
            if ($matchedTokensCount < $translatorTokensCount) {
                if ($this->tokensEqual($token, $translatorTokens[$matchedTokensCount])) {
                    $matchedTokensCount++;
                } else {
                    $matchedTokensCount = 0;
                }
            } elseif ($matchedTokensCount === $translatorTokensCount) {
                // translator found

                // end of function call
                if ($this->tokensEqual(')', $token)) {
                    $pendingParenthesisCount--;

                    if ($pendingParenthesisCount === 0) {
                        // end of translator call or end of something that we can't extract
                        if (isset($buffer[0][0], $buffer[1], $buffer[2][0]) && $buffer[0][0] === T_CONSTANT_ENCAPSED_STRING && $buffer[1] === ',' && $buffer[2][0] === T_CONSTANT_ENCAPSED_STRING) {
                            // is valid call we can extract
                            $category = stripcslashes($buffer[0][1]);
                            $category = mb_substr($category, 1, mb_strlen($category) - 2);

                            if (!$this->isCategoryIgnored($category, $ignoreCategories)) {
                                $message = stripcslashes($buffer[2][1]);
                                $message = mb_substr($message, 1, mb_strlen($message) - 2);

                                $messages[$category][] = $message;
                            }

                            $nestedTokens = array_slice($buffer, 3);
                            if (count($nestedTokens) > $translatorTokensCount) {
                                // search for possible nested translator calls
                                $messages = array_merge_recursive($messages, $this->extractMessagesFromTokens($nestedTokens, $translatorTokens, $ignoreCategories));
                            }
                        } else {
                            // invalid call or dynamic call we can't extract
                            $line = Console::ansiFormat($this->getLine($buffer), [Console::FG_CYAN]);
                            $skipping = Console::ansiFormat('Skipping line', [Console::FG_YELLOW]);
                            $this->stdout("$skipping $line. Make sure both category and message are static strings.\n");
                        }

                        // prepare for the next match
                        $matchedTokensCount = 0;
                        $pendingParenthesisCount = 0;
                        $buffer = [];
                    } else {
                        $buffer[] = $token;
                    }
                } elseif ($this->tokensEqual('(', $token)) {
                    // count beginning of function call, skipping translator beginning
                    if ($pendingParenthesisCount > 0) {
                        $buffer[] = $token;
                    }
                    $pendingParenthesisCount++;
                } elseif (isset($token[0]) && !in_array($token[0], [T_WHITESPACE, T_COMMENT])) {
                    // ignore comments and whitespaces
                    $buffer[] = $token;
                }
            }
        }

        return $messages;
    }

    
    protected function isCategoryIgnored($category, array $ignoreCategories)
    {
        $result = false;

        if (!empty($ignoreCategories)) {
            if (in_array($category, $ignoreCategories, true)) {
                $result = true;
            } else {
                foreach ($ignoreCategories as $pattern) {
                    if (strpos($pattern, '*') > 0 && strpos($category, rtrim($pattern, '*')) === 0) {
                        $result = true;
                        break;
                    }
                }
            }
        }

        return $result;
    }

    
    protected function tokensEqual($a, $b)
    {
        if (is_string($a) && is_string($b)) {
            return $a === $b;
        } elseif (isset($a[0], $a[1], $b[0], $b[1])) {
            return $a[0] === $b[0] && $a[1] == $b[1];
        }
        return false;
    }

    
    protected function getLine($tokens)
    {
        foreach ($tokens as $token) {
            if (isset($token[2])) {
                return $token[2];
            }
        }
        return 'unknown';
    }

    
    protected function saveMessagesToPHP($messages, $dirName, $overwrite, $removeUnused, $sort, $markUnused)
    {
        foreach ($messages as $category => $msgs) {
            $file = str_replace("\\", '/', "$dirName/$category.php");
            $path = dirname($file);
            FileHelper::createDirectory($path);
            $msgs = array_values(array_unique($msgs));
            $coloredFileName = Console::ansiFormat($file, [Console::FG_CYAN]);
            $this->stdout("Saving messages to $coloredFileName...\n");
            $this->saveMessagesCategoryToPHP($msgs, $file, $overwrite, $removeUnused, $sort, $category, $markUnused);
        }
    }

    
    protected function saveMessagesCategoryToPHP($messages, $fileName, $overwrite, $removeUnused, $sort, $category, $markUnused)
    {
        if (is_file($fileName)) {
            $rawExistingMessages = require($fileName);
            $existingMessages = $rawExistingMessages;
            sort($messages);
            ksort($existingMessages);
            if (array_keys($existingMessages) === $messages && (!$sort || array_keys($rawExistingMessages) === $messages)) {
                $this->stdout("Nothing new in \"$category\" category... Nothing to save.\n\n", Console::FG_GREEN);
                return self::EXIT_CODE_NORMAL;
            }
            unset($rawExistingMessages);
            $merged = [];
            $untranslated = [];
            foreach ($messages as $message) {
                if (array_key_exists($message, $existingMessages) && $existingMessages[$message] !== '') {
                    $merged[$message] = $existingMessages[$message];
                } else {
                    $untranslated[] = $message;
                }
            }
            ksort($merged);
            sort($untranslated);
            $todo = [];
            foreach ($untranslated as $message) {
                $todo[$message] = '';
            }
            ksort($existingMessages);
            foreach ($existingMessages as $message => $translation) {
                if (!$removeUnused && !isset($merged[$message]) && !isset($todo[$message])) {
                    if (!empty($translation) && (!$markUnused || (strncmp($translation, '@@', 2) === 0 && substr_compare($translation, '@@', -2, 2) === 0))) {
                        $todo[$message] = $translation;
                    } else {
                        $todo[$message] = '@@' . $translation . '@@';
                    }
                }
            }
            $merged = array_merge($todo, $merged);
            if ($sort) {
                ksort($merged);
            }
            if (false === $overwrite) {
                $fileName .= '.merged';
            }
            $this->stdout("Translation merged.\n");
        } else {
            $merged = [];
            foreach ($messages as $message) {
                $merged[$message] = '';
            }
            ksort($merged);
        }


        $array = VarDumper::export($merged);
        $content = <<<EOD
<?php

return $array;

EOD;

        if (file_put_contents($fileName, $content) !== false) {
            $this->stdout("Translation saved.\n\n", Console::FG_GREEN);
            return self::EXIT_CODE_NORMAL;
        } else {
            $this->stdout("Translation was NOT saved.\n\n", Console::FG_RED);
            return self::EXIT_CODE_ERROR;
        }
    }

    
    protected function saveMessagesToPO($messages, $dirName, $overwrite, $removeUnused, $sort, $catalog, $markUnused)
    {
        $file = str_replace("\\", '/', "$dirName/$catalog.po");
        FileHelper::createDirectory(dirname($file));
        $this->stdout("Saving messages to $file...\n");

        $poFile = new GettextPoFile();


        $merged = [];
        $todos = [];

        $hasSomethingToWrite = false;
        foreach ($messages as $category => $msgs) {
            $notTranslatedYet = [];
            $msgs = array_values(array_unique($msgs));

            if (is_file($file)) {
                $existingMessages = $poFile->load($file, $category);

                sort($msgs);
                ksort($existingMessages);
                if (array_keys($existingMessages) == $msgs) {
                    $this->stdout("Nothing new in \"$category\" category...\n");

                    sort($msgs);
                    foreach ($msgs as $message) {
                        $merged[$category . chr(4) . $message] = $existingMessages[$message];
                    }
                    ksort($merged);
                    continue;
                }

                // merge existing message translations with new message translations
                foreach ($msgs as $message) {
                    if (array_key_exists($message, $existingMessages) && $existingMessages[$message] !== '') {
                        $merged[$category . chr(4) . $message] = $existingMessages[$message];
                    } else {
                        $notTranslatedYet[] = $message;
                    }
                }
                ksort($merged);
                sort($notTranslatedYet);

                // collect not yet translated messages
                foreach ($notTranslatedYet as $message) {
                    $todos[$category . chr(4) . $message] = '';
                }

                // add obsolete unused messages
                foreach ($existingMessages as $message => $translation) {
                    if (!$removeUnused && !isset($merged[$category . chr(4) . $message]) && !isset($todos[$category . chr(4) . $message])) {
                        if (!empty($translation) && (!$markUnused || (substr($translation, 0, 2) === '@@' && substr($translation, -2) === '@@'))) {
                            $todos[$category . chr(4) . $message] = $translation;
                        } else {
                            $todos[$category . chr(4) . $message] = '@@' . $translation . '@@';
                        }
                    }
                }

                $merged = array_merge($todos, $merged);
                if ($sort) {
                    ksort($merged);
                }

                if ($overwrite === false) {
                    $file .= '.merged';
                }
            } else {
                sort($msgs);
                foreach ($msgs as $message) {
                    $merged[$category . chr(4) . $message] = '';
                }
                ksort($merged);
            }
            $this->stdout("Category \"$category\" merged.\n");
            $hasSomethingToWrite = true;
        }
        if ($hasSomethingToWrite) {
            $poFile->save($file, $merged);
            $this->stdout("Translation saved.\n", Console::FG_GREEN);
        } else {
            $this->stdout("Nothing to save.\n", Console::FG_GREEN);
        }
    }

    
    protected function saveMessagesToPOT($messages, $dirName, $catalog)
    {
        $file = str_replace("\\", '/', "$dirName/$catalog.pot");
        FileHelper::createDirectory(dirname($file));
        $this->stdout("Saving messages to $file...\n");

        $poFile = new GettextPoFile();

        $merged = [];

        $hasSomethingToWrite = false;
        foreach ($messages as $category => $msgs) {
            $msgs = array_values(array_unique($msgs));

            sort($msgs);
            foreach ($msgs as $message) {
                $merged[$category . chr(4) . $message] = '';
            }
            $this->stdout("Category \"$category\" merged.\n");
            $hasSomethingToWrite = true;
        }
        if ($hasSomethingToWrite) {
            ksort($merged);
            $poFile->save($file, $merged);
            $this->stdout("Translation saved.\n", Console::FG_GREEN);
        } else {
            $this->stdout("Nothing to save.\n", Console::FG_GREEN);
        }
    }
}
