<?php

namespace Codeception\Util;

use Codeception\Exception\ConfigurationException;

class PathResolver
{
    
    public static function getRelativeDir($path, $projDir, $dirSep = DIRECTORY_SEPARATOR)
    {
        // ensure $projDir ends with a trailing $dirSep
        $projDir = preg_replace('/'.preg_quote($dirSep, '/').'*$/', $dirSep, $projDir);
        // if $path is a within $projDir
        if (self::fsCaseStrCmp(substr($path, 0, strlen($projDir)), $projDir, $dirSep) == 0) {
            // simply chop it off the front
            return substr($path, strlen($projDir));
        }
        // Identify any absoluteness prefix (like '/' in Unix or "C:\\" in Windows)
        $pathAbsPrefix = self::getPathAbsolutenessPrefix($path, $dirSep);
        $projDirAbsPrefix = self::getPathAbsolutenessPrefix($projDir, $dirSep);
        $sameAbsoluteness = (self::fsCaseStrCmp($pathAbsPrefix['wholePrefix'], $projDirAbsPrefix['wholePrefix'], $dirSep) == 0);
        if (!$sameAbsoluteness) {
            // if the $projDir and $path aren't relative to the same
            // thing, we can't make a relative path.

            // if we're relative to the same device ...
            if (strlen($pathAbsPrefix['devicePrefix']) &&
                (self::fsCaseStrCmp($pathAbsPrefix['devicePrefix'], $projDirAbsPrefix['devicePrefix'], $dirSep) == 0)
            ) {
                // ... shave that off
                return substr($path, strlen($pathAbsPrefix['devicePrefix']));
            }
            // Return the input unaltered
            return $path;
        }
        // peel off optional absoluteness prefixes and convert
        // $path and $projDir to an subdirectory path array
        $relPathParts = array_filter(explode($dirSep, substr($path, strlen($pathAbsPrefix['wholePrefix']))), 'strlen');
        $relProjDirParts = array_filter(explode($dirSep, substr($projDir, strlen($projDirAbsPrefix['wholePrefix']))), 'strlen');
        // While there are any, peel off any common parent directories
        // from the beginning of the $projDir and $path
        while ((count($relPathParts) > 0) && (count($relProjDirParts) > 0) &&
            (self::fsCaseStrCmp($relPathParts[0], $relProjDirParts[0], $dirSep) == 0)
        ) {
            array_shift($relPathParts);
            array_shift($relProjDirParts);
        }
        if (count($relProjDirParts) > 0) {
            // prefix $relPath with '..' for all remaining unmatched $projDir
            // subdirectories
            $relPathParts = array_merge(array_fill(0, count($relProjDirParts), '..'), $relPathParts);
        }
        // only append a trailing seperator if one is already present
        $trailingSep = preg_match('/'.preg_quote($dirSep, '/').'$/', $path) ? $dirSep : '';
        // convert array of dir paths back into a string path
        return implode($dirSep, $relPathParts).$trailingSep;
    }
    
    private static function fsCaseStrCmp($str1, $str2, $dirSep = DIRECTORY_SEPARATOR)
    {
        $cmpFn = self::isWindows($dirSep) ? 'strcasecmp' : 'strcmp';
        return $cmpFn($str1, $str2);
    }

    
    private static function getPathAbsolutenessPrefix($path, $dirSep = DIRECTORY_SEPARATOR)
    {
        $devLetterPrefixPattern = '';
        if (self::isWindows($dirSep)) {
            $devLetterPrefixPattern = '([A-Za-z]:|)';
        }
        $matches = [];
        if (!preg_match('/^'.$devLetterPrefixPattern.preg_quote($dirSep, '/').'?/', $path, $matches)) {
            // This should match, even if it matches 0 characters
            throw new ConfigurationException("INTERNAL ERROR: This must be a regex problem.");
        }
        return [
            'wholePrefix'  => $matches[0], // The optional device letter followed by the optional $dirSep
            'devicePrefix' => self::isWindows($dirSep) ? $matches[1] : ''];
    }

    
    private static function isWindows($dirSep = DIRECTORY_SEPARATOR)
    {
        return ($dirSep == '\\');
    }
}
