#!/usr/bin/php
<?php

chdir(dirname(__FILE__));
require_once 'common.php';
assertCli();




$GLOBALS['loaded'] = array();


class MergeLibraryFSTools extends FSTools
{
    public function copyable($entry)
    {
        // Skip hidden files
        if ($entry[0] == '.') {
            return false;
        }
        return true;
    }
    public function copy($source, $dest)
    {
        copy_and_remove_includes($source, $dest);
    }
}
$FS = new MergeLibraryFSTools();


function replace_includes($text)
{
    // also remove vim modelines
    return preg_replace_callback(
        "/require(?:_once)? ['\"]([^'\"]+)['\"];/",
        'replace_includes_callback',
        $text
    );
}


function remove_php_tags($text)
{
    $text = preg_replace('#// vim:.+#', '', $text);
    return substr($text, 5);
}


function make_dir_standalone($dir)
{
    global $FS;
    return $FS->copyr($dir, 'standalone/' . $dir);
}


function make_file_standalone($file)
{
    global $FS;
    $FS->mkdirr('standalone/' . dirname($file));
    copy_and_remove_includes($file, 'standalone/' . $file);
    return true;
}


function copy_and_remove_includes($file, $sfile)
{
    $contents = file_get_contents($file);
    if (strrchr($file, '.') === '.php') $contents = replace_includes($contents);
    return file_put_contents($sfile, $contents);
}


function replace_includes_callback($matches)
{
    $file = $matches[1];
    $preserve = array(
      // PEAR (external)
      'XML/HTMLSax3.php' => 1
    );
    if (isset($preserve[$file])) {
        return $matches[0];
    }
    if (isset($GLOBALS['loaded'][$file])) return '';
    $GLOBALS['loaded'][$file] = true;
    return replace_includes(remove_php_tags(file_get_contents($file)));
}

echo 'Generating includes file... ';
shell_exec('php generate-includes.php');
echo "done!\n";

chdir(dirname(__FILE__) . '/../library/');

echo 'Creating full file...';
$contents = replace_includes(file_get_contents('HTMLPurifier.includes.php'));
$contents = str_replace(
    // Note that bootstrap is now inside the standalone file
    "define('HTMLPURIFIER_PREFIX', realpath(dirname(__FILE__) . '/..'));",
    "define('HTMLPURIFIER_PREFIX', dirname(__FILE__) . '/standalone');
    set_include_path(HTMLPURIFIER_PREFIX . PATH_SEPARATOR . get_include_path());",
    $contents
);
file_put_contents('HTMLPurifier.standalone.php', $contents);
echo ' done!' . PHP_EOL;

echo 'Creating standalone directory...';
$FS->rmdirr('standalone'); // ensure a clean copy

// data files
$FS->mkdirr('standalone/HTMLPurifier/DefinitionCache/Serializer');
make_file_standalone('HTMLPurifier/EntityLookup/entities.ser');
make_file_standalone('HTMLPurifier/ConfigSchema/schema.ser');

// non-standard inclusion setup
make_dir_standalone('HTMLPurifier/ConfigSchema');
make_dir_standalone('HTMLPurifier/Language');
make_dir_standalone('HTMLPurifier/Filter');
make_dir_standalone('HTMLPurifier/Printer');
make_file_standalone('HTMLPurifier/Printer.php');
make_file_standalone('HTMLPurifier/Lexer/PH5P.php');

echo ' done!' . PHP_EOL;

// vim: et sw=4 sts=4
