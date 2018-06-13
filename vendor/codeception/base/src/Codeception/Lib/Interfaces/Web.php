<?php
namespace Codeception\Lib\Interfaces;

interface Web
{
    
    public function amOnPage($page);

    
    public function see($text, $selector = null);

    
    public function dontSee($text, $selector = null);
    
    
    public function seeInSource($raw);

    
    public function dontSeeInSource($raw);

    
    public function submitForm($selector, array $params, $button = null);

    
    public function click($link, $context = null);

    
    public function seeLink($text, $url = null);

    
    public function dontSeeLink($text, $url = null);

    
    public function seeInCurrentUrl($uri);

    
    public function seeCurrentUrlEquals($uri);

    
    public function seeCurrentUrlMatches($uri);

    
    public function dontSeeInCurrentUrl($uri);

    
    public function dontSeeCurrentUrlEquals($uri);

    
    public function dontSeeCurrentUrlMatches($uri);

    
    public function grabFromCurrentUrl($uri = null);

    
    public function seeCheckboxIsChecked($checkbox);

    
    public function dontSeeCheckboxIsChecked($checkbox);

    
    public function seeInField($field, $value);

    
    public function dontSeeInField($field, $value);

    
    public function seeInFormFields($formSelector, array $params);

    
    public function dontSeeInFormFields($formSelector, array $params);

    
    public function selectOption($select, $option);

    
    public function checkOption($option);

    
    public function uncheckOption($option);

    
    public function fillField($field, $value);

    
    public function attachFile($field, $filename);

    
    public function grabTextFrom($cssOrXPathOrRegex);

    
    public function grabValueFrom($field);


    
    public function grabAttributeFrom($cssOrXpath, $attribute);
    
    
    public function grabMultiple($cssOrXpath, $attribute = null);

    
    public function seeElement($selector, $attributes = []);

    
    public function dontSeeElement($selector, $attributes = []);

    
    public function seeNumberOfElements($selector, $expected);

    
    public function seeOptionIsSelected($selector, $optionText);

    
    public function dontSeeOptionIsSelected($selector, $optionText);

    
    public function seeInTitle($title);

    
    public function dontSeeInTitle($title);

    
    public function seeCookie($cookie, array $params = []);

    
    public function dontSeeCookie($cookie, array $params = []);

    
    public function setCookie($name, $val, array $params = []);

    
    public function resetCookie($cookie, array $params = []);

    
    public function grabCookie($cookie, array $params = []);
}
