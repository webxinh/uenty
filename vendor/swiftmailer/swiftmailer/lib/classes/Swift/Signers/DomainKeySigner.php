<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Signers_DomainKeySigner implements Swift_Signers_HeaderSigner
{
    
    protected $_privateKey;

    
    protected $_domainName;

    
    protected $_selector;

    
    protected $_hashAlgorithm = 'rsa-sha1';

    
    protected $_canon = 'simple';

    
    protected $_ignoredHeaders = array();

    
    protected $_signerIdentity;

    
    protected $_debugHeaders = false;

    // work variables
    
    private $_signedHeaders = array();

    
    protected $_domainKeyHeader;

    
    private $_hashHandler;

    private $_hash;

    private $_canonData = '';

    private $_bodyCanonEmptyCounter = 0;

    private $_bodyCanonIgnoreStart = 2;

    private $_bodyCanonSpace = false;

    private $_bodyCanonLastChar = null;

    private $_bodyCanonLine = '';

    private $_bound = array();

    
    public function __construct($privateKey, $domainName, $selector)
    {
        $this->_privateKey = $privateKey;
        $this->_domainName = $domainName;
        $this->_signerIdentity = '@'.$domainName;
        $this->_selector = $selector;
    }

    
    public static function newInstance($privateKey, $domainName, $selector)
    {
        return new static($privateKey, $domainName, $selector);
    }

    
    public function reset()
    {
        $this->_hash = null;
        $this->_hashHandler = null;
        $this->_bodyCanonIgnoreStart = 2;
        $this->_bodyCanonEmptyCounter = 0;
        $this->_bodyCanonLastChar = null;
        $this->_bodyCanonSpace = false;

        return $this;
    }

    
    public function write($bytes)
    {
        $this->_canonicalizeBody($bytes);
        foreach ($this->_bound as $is) {
            $is->write($bytes);
        }

        return $this;
    }

    
    public function commit()
    {
        // Nothing to do
        return $this;
    }

    
    public function bind(Swift_InputByteStream $is)
    {
        // Don't have to mirror anything
        $this->_bound[] = $is;

        return $this;
    }

    
    public function unbind(Swift_InputByteStream $is)
    {
        // Don't have to mirror anything
        foreach ($this->_bound as $k => $stream) {
            if ($stream === $is) {
                unset($this->_bound[$k]);

                return;
            }
        }

        return $this;
    }

    
    public function flushBuffers()
    {
        $this->reset();

        return $this;
    }

    
    public function setHashAlgorithm($hash)
    {
        $this->_hashAlgorithm = 'rsa-sha1';

        return $this;
    }

    
    public function setCanon($canon)
    {
        if ($canon == 'nofws') {
            $this->_canon = 'nofws';
        } else {
            $this->_canon = 'simple';
        }

        return $this;
    }

    
    public function setSignerIdentity($identity)
    {
        $this->_signerIdentity = $identity;

        return $this;
    }

    
    public function setDebugHeaders($debug)
    {
        $this->_debugHeaders = (bool) $debug;

        return $this;
    }

    
    public function startBody()
    {
    }

    
    public function endBody()
    {
        $this->_endOfBody();
    }

    
    public function getAlteredHeaders()
    {
        if ($this->_debugHeaders) {
            return array('DomainKey-Signature', 'X-DebugHash');
        }

        return array('DomainKey-Signature');
    }

    
    public function ignoreHeader($header_name)
    {
        $this->_ignoredHeaders[strtolower($header_name)] = true;

        return $this;
    }

    
    public function setHeaders(Swift_Mime_HeaderSet $headers)
    {
        $this->_startHash();
        $this->_canonData = '';
        // Loop through Headers
        $listHeaders = $headers->listAll();
        foreach ($listHeaders as $hName) {
            // Check if we need to ignore Header
            if (!isset($this->_ignoredHeaders[strtolower($hName)])) {
                if ($headers->has($hName)) {
                    $tmp = $headers->getAll($hName);
                    foreach ($tmp as $header) {
                        if ($header->getFieldBody() != '') {
                            $this->_addHeader($header->toString());
                            $this->_signedHeaders[] = $header->getFieldName();
                        }
                    }
                }
            }
        }
        $this->_endOfHeaders();

        return $this;
    }

    
    public function addSignature(Swift_Mime_HeaderSet $headers)
    {
        // Prepare the DomainKey-Signature Header
        $params = array('a' => $this->_hashAlgorithm, 'b' => chunk_split(base64_encode($this->_getEncryptedHash()), 73, ' '), 'c' => $this->_canon, 'd' => $this->_domainName, 'h' => implode(': ', $this->_signedHeaders), 'q' => 'dns', 's' => $this->_selector);
        $string = '';
        foreach ($params as $k => $v) {
            $string .= $k.'='.$v.'; ';
        }
        $string = trim($string);
        $headers->addTextHeader('DomainKey-Signature', $string);

        return $this;
    }

    /* Private helpers */

    protected function _addHeader($header)
    {
        switch ($this->_canon) {
            case 'nofws':
                // Prepare Header and cascade
                $exploded = explode(':', $header, 2);
                $name = strtolower(trim($exploded[0]));
                $value = str_replace("\r\n", '', $exploded[1]);
                $value = preg_replace("/[ \t][ \t]+/", ' ', $value);
                $header = $name.':'.trim($value)."\r\n";
            case 'simple':
                // Nothing to do
        }
        $this->_addToHash($header);
    }

    protected function _endOfHeaders()
    {
        $this->_bodyCanonEmptyCounter = 1;
    }

    protected function _canonicalizeBody($string)
    {
        $len = strlen($string);
        $canon = '';
        $nofws = ($this->_canon == 'nofws');
        for ($i = 0; $i < $len; ++$i) {
            if ($this->_bodyCanonIgnoreStart > 0) {
                --$this->_bodyCanonIgnoreStart;
                continue;
            }
            switch ($string[$i]) {
                case "\r":
                    $this->_bodyCanonLastChar = "\r";
                    break;
                case "\n":
                    if ($this->_bodyCanonLastChar == "\r") {
                        if ($nofws) {
                            $this->_bodyCanonSpace = false;
                        }
                        if ($this->_bodyCanonLine == '') {
                            ++$this->_bodyCanonEmptyCounter;
                        } else {
                            $this->_bodyCanonLine = '';
                            $canon .= "\r\n";
                        }
                    } else {
                        // Wooops Error
                        throw new Swift_SwiftException('Invalid new line sequence in mail found \n without preceding \r');
                    }
                    break;
                case ' ':
                case "\t":
                case "\x09": //HTAB
                    if ($nofws) {
                        $this->_bodyCanonSpace = true;
                        break;
                    }
                default:
                    if ($this->_bodyCanonEmptyCounter > 0) {
                        $canon .= str_repeat("\r\n", $this->_bodyCanonEmptyCounter);
                        $this->_bodyCanonEmptyCounter = 0;
                    }
                    $this->_bodyCanonLine .= $string[$i];
                    $canon .= $string[$i];
            }
        }
        $this->_addToHash($canon);
    }

    protected function _endOfBody()
    {
        if (strlen($this->_bodyCanonLine) > 0) {
            $this->_addToHash("\r\n");
        }
        $this->_hash = hash_final($this->_hashHandler, true);
    }

    private function _addToHash($string)
    {
        $this->_canonData .= $string;
        hash_update($this->_hashHandler, $string);
    }

    private function _startHash()
    {
        // Init
        switch ($this->_hashAlgorithm) {
            case 'rsa-sha1':
                $this->_hashHandler = hash_init('sha1');
                break;
        }
        $this->_bodyCanonLine = '';
    }

    
    private function _getEncryptedHash()
    {
        $signature = '';
        $pkeyId = openssl_get_privatekey($this->_privateKey);
        if (!$pkeyId) {
            throw new Swift_SwiftException('Unable to load DomainKey Private Key ['.openssl_error_string().']');
        }
        if (openssl_sign($this->_canonData, $signature, $pkeyId, OPENSSL_ALGO_SHA1)) {
            return $signature;
        }
        throw new Swift_SwiftException('Unable to sign DomainKey Hash  ['.openssl_error_string().']');
    }
}
