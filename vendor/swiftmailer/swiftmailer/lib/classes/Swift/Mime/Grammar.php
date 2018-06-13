<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Mime_Grammar
{
    
    private static $_specials = array();

    
    private static $_grammar = array();

    
    public function __construct()
    {
        $this->init();
    }

    public function __wakeup()
    {
        $this->init();
    }

    protected function init()
    {
        if (count(self::$_specials) > 0) {
            return;
        }

        self::$_specials = array(
            '(', ')', '<', '>', '[', ']',
            ':', ';', '@', ',', '.', '"',
            );

        

        // All basic building blocks
        self::$_grammar['NO-WS-CTL'] = '[\x01-\x08\x0B\x0C\x0E-\x19\x7F]';
        self::$_grammar['WSP'] = '[ \t]';
        self::$_grammar['CRLF'] = '(?:\r\n)';
        self::$_grammar['FWS'] = '(?:(?:'.self::$_grammar['WSP'].'*'.
                self::$_grammar['CRLF'].')?'.self::$_grammar['WSP'].')';
        self::$_grammar['text'] = '[\x00-\x08\x0B\x0C\x0E-\x7F]';
        self::$_grammar['quoted-pair'] = '(?:\\\\'.self::$_grammar['text'].')';
        self::$_grammar['ctext'] = '(?:'.self::$_grammar['NO-WS-CTL'].
                '|[\x21-\x27\x2A-\x5B\x5D-\x7E])';
        // Uses recursive PCRE (?1) -- could be a weak point??
        self::$_grammar['ccontent'] = '(?:'.self::$_grammar['ctext'].'|'.
                self::$_grammar['quoted-pair'].'|(?1))';
        self::$_grammar['comment'] = '(\((?:'.self::$_grammar['FWS'].'|'.
                self::$_grammar['ccontent'].')*'.self::$_grammar['FWS'].'?\))';
        self::$_grammar['CFWS'] = '(?:(?:'.self::$_grammar['FWS'].'?'.
                self::$_grammar['comment'].')*(?:(?:'.self::$_grammar['FWS'].'?'.
                self::$_grammar['comment'].')|'.self::$_grammar['FWS'].'))';
        self::$_grammar['qtext'] = '(?:'.self::$_grammar['NO-WS-CTL'].
                '|[\x21\x23-\x5B\x5D-\x7E])';
        self::$_grammar['qcontent'] = '(?:'.self::$_grammar['qtext'].'|'.
                self::$_grammar['quoted-pair'].')';
        self::$_grammar['quoted-string'] = '(?:'.self::$_grammar['CFWS'].'?"'.
                '('.self::$_grammar['FWS'].'?'.self::$_grammar['qcontent'].')*'.
                self::$_grammar['FWS'].'?"'.self::$_grammar['CFWS'].'?)';
        self::$_grammar['atext'] = '[a-zA-Z0-9!#\$%&\'\*\+\-\/=\?\^_`\{\}\|~]';
        self::$_grammar['atom'] = '(?:'.self::$_grammar['CFWS'].'?'.
                self::$_grammar['atext'].'+'.self::$_grammar['CFWS'].'?)';
        self::$_grammar['dot-atom-text'] = '(?:'.self::$_grammar['atext'].'+'.
                '(\.'.self::$_grammar['atext'].'+)*)';
        self::$_grammar['dot-atom'] = '(?:'.self::$_grammar['CFWS'].'?'.
                self::$_grammar['dot-atom-text'].'+'.self::$_grammar['CFWS'].'?)';
        self::$_grammar['word'] = '(?:'.self::$_grammar['atom'].'|'.
                self::$_grammar['quoted-string'].')';
        self::$_grammar['phrase'] = '(?:'.self::$_grammar['word'].'+?)';
        self::$_grammar['no-fold-quote'] = '(?:"(?:'.self::$_grammar['qtext'].
                '|'.self::$_grammar['quoted-pair'].')*")';
        self::$_grammar['dtext'] = '(?:'.self::$_grammar['NO-WS-CTL'].
                '|[\x21-\x5A\x5E-\x7E])';
        self::$_grammar['no-fold-literal'] = '(?:\[(?:'.self::$_grammar['dtext'].
                '|'.self::$_grammar['quoted-pair'].')*\])';

        // Message IDs
        self::$_grammar['id-left'] = '(?:'.self::$_grammar['dot-atom-text'].'|'.
                self::$_grammar['no-fold-quote'].')';
        self::$_grammar['id-right'] = '(?:'.self::$_grammar['dot-atom-text'].'|'.
                self::$_grammar['no-fold-literal'].')';

        // Addresses, mailboxes and paths
        self::$_grammar['local-part'] = '(?:'.self::$_grammar['dot-atom'].'|'.
                self::$_grammar['quoted-string'].')';
        self::$_grammar['dcontent'] = '(?:'.self::$_grammar['dtext'].'|'.
                self::$_grammar['quoted-pair'].')';
        self::$_grammar['domain-literal'] = '(?:'.self::$_grammar['CFWS'].'?\[('.
                self::$_grammar['FWS'].'?'.self::$_grammar['dcontent'].')*?'.
                self::$_grammar['FWS'].'?\]'.self::$_grammar['CFWS'].'?)';
        self::$_grammar['domain'] = '(?:'.self::$_grammar['dot-atom'].'|'.
                self::$_grammar['domain-literal'].')';
        self::$_grammar['addr-spec'] = '(?:'.self::$_grammar['local-part'].'@'.
                self::$_grammar['domain'].')';
    }

    
    public function getDefinition($name)
    {
        if (array_key_exists($name, self::$_grammar)) {
            return self::$_grammar[$name];
        }

        throw new Swift_RfcComplianceException(
            "No such grammar '".$name."' defined."
        );
    }

    
    public function getGrammarDefinitions()
    {
        return self::$_grammar;
    }

    
    public function getSpecials()
    {
        return self::$_specials;
    }

    
    public function escapeSpecials($token, $include = array(), $exclude = array())
    {
        foreach (array_merge(array('\\'), array_diff(self::$_specials, $exclude), $include) as $char) {
            $token = str_replace($char, '\\'.$char, $token);
        }

        return $token;
    }
}
