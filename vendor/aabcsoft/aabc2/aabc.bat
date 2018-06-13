@echo off

rem -------------------------------------------------------------
rem  aabc command line bootstrap script for Windows.
rem
rem  @author Qiang Xue <qiang.xue@gmail.com>
rem  @link http://www.aabcframework.com/
rem  @copyright Copyright (c) 2008 aabc Software LLC
rem  @license http://www.aabcframework.com/license/
rem -------------------------------------------------------------

@setlocal

set AABC_PATH=%~dp0

if "%PHP_COMMAND%" == "" set PHP_COMMAND=php.exe

"%PHP_COMMAND%" "%AABC_PATH%aabc" %*

@endlocal
