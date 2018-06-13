<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Signers_DKIMSigner implements Swift_Signers_HeaderSigner
{
    
    protected $_privateKey;

    
    protected $_domainName;

    
    protected $_selector;

    
    protected $_hashAlgorithm = 'rsa-sha1';

    
    protected $_bodyCanon = 'simple';

    
    protected $_headerCanon = 'simple';

    
    protected $_ignoredHeaders = array('return-path' => true);

    
    protected $_signerIdentity;

    
    protected $_bodyLen = 0;

    
    protected $_maxLen = PHP_INT_MAX;

    
    protected $_showLen = false;

    
    protected $_signatureTimestamp = true;

    
    protected $_signatureExpiration = false;

    
    protected $_debugHeaders = false;

    // work variables
    
    protected $_signedHeaders = array();

    
    private $_debugHeadersData = '';

    
    private $_bodyHash = '';

    
    protected $_dkimHeader;

    private $_bodyHashHandler;

    private $_headerHash;

    private $_headerCanonData = '';

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
        $this->_headerHash = null;
        $this->_signedHeaders = array();
        $this->_bodyHash = null;
        $this->_bodyHashHandler = null;
        $this->_bodyCanonIgnoreStart = 2;
        $this->_bodyCanonEmptyCounter = 0;
        $this->_bodyCanonLastChar = null;
        $this->_bodyCanonSpace = false;
    }

    
    public function write($bytes)
    {
        $this->_canonicalizeBody($bytes);
        foreach ($this->_bound as $is) {
            $is->write($bytes);
        }
    }

    
    public function commit()
    {
        // Nothing to do
        return;
    }

    
    public function bind(Swift_InputByteStream $is)
    {
        // Don't have to mirror anything
        $this->_bound[] = $is;

        return;
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

        return;
    }

    
    public function flushBuffers()
    {
        $this->reset();
    }

    
    public function setHashAlgorithm($hash)
    {
        // Unable to sign with rsa-sha256
        if ($hash == 'rsa-sha1') {
            $this->_hashAlgorithm = 'rsa-sha1';
        } else {
            $this->_hashAlgorithm = 'rsa-sha256';
        }

        return $this;
    }

    
    public function setBodyCanon($canon)
    {
        if ($canon == 'relaxed') {
            $this->_bodyCanon = 'relaxed';
        } else {
            $this->_bodyCanon = 'simple';
        }

        return $this;
    }

    
    public function setHeaderCanon($canon)
    {
        if ($canon == 'relaxed') {
            $this->_headerCanon = 'relaxed';
        } else {
            $this->_headerCanon = 'simple';
        }

        return $this;
    }

    
    public function setSignerIdentity($identity)
    {
        $this->_signerIdentity = $identity;

        return $this;
    }

    
    public function setBodySignedLen($len)
    {
        if ($len === true) {
            $this->_showLen = true;
            $this->_maxLen = PHP_INT_MAX;
        } elseif ($len === false) {
            $this->_showLen = false;
            $this->_maxLen = PHP_INT_MAX;
        } else {
            $this->_showLen = true;
            $this->_maxLen = (int) $len;
        }

        return $this;
    }

    
    public function setSignatureTimestamp($time)
    {
        $this->_signatureTimestamp = $time;

        return $this;
    }

    
    public function setSignatureExpiration($time)
    {
        $this->_signatureExpiration = $time;

        return $this;
    }

    
    public function setDebugHeaders($debug)
    {
        $this->_debugHeaders = (bool) $debug;

        return $this;
    }

    
    public function startBody()
    {
        // Init
        switch ($this->_hashAlgorithm) {
            case 'rsa-sha256':
                $this->_bodyHashHandler = hash_init('sha256');
                break;
            case 'rsa-sha1':
                $this->_bodyHashHandler = hash_init('sha1');
                break;
        }
        $this->_bodyCanonLine = '';
    }

    
    public function endBody()
    {
        $this->_endOfBody();
    }

    
    public function getAlteredHeaders()
    {
        if ($this->_debugHeaders) {
            return array('DKIM-Signature', 'X-DebugHash');
        } else {
            return array('DKIM-Signature');
        }
    }

    
    public function ignoreHeader($header_name)
    {
        $this->_ignoredHeaders[strtolower($header_name)] = true;

        return $this;
    }

    
    public function setHeaders(Swift_Mime_HeaderSet $headers)
    {
        $this->_headerCanonData = '';
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

        return $this;
    }

    
    public function addSignature(Swift_Mime_HeaderSet $headers)
    {
        // Prepare the DKIM-Signature
        $params = array('v' => '1', 'a' => $this->_hashAlgorithm, 'bh' => base64_encode($this->_bodyHash), 'd' => $this->_domainName, 'h' => implode(': ', $this->_signedHeaders), 'i' => $this->_signerIdentity, 's' => $this->_selector);
        if ($this->_bodyCanon != 'simple') {
            $params['c'] = $this->_headerCanon.'/'.$this->_bodyCanon;
        } elseif ($this->_headerCanon != 'simple') {
            $params['c'] = $this->_headerCanon;
        }
        if ($this->_showLen) {
            $params['l'] = $this->_bodyLen;
        }
        if ($this->_signatureTimestamp === true) {
            $params['t'] = time();
            if ($this->_signatureExpiration !== false) {
                $params['x'] = $params['t'] + $this->_signatureExpiration;
            }
        } else {
            if ($this->_signatureTimestamp !== false) {
                $params['t'] = $this->_signatureTimestamp;
            }
            if ($this->_signatureExpiration !== false) {
                $params['x'] = $this->_signatureExpiration;
            }
        }
        if ($this->_debugHeaders) {
            $params['z'] = implode('|', $this->_debugHeadersData);
        }
        $string = '';
        foreach ($params as $k => $v) {
            $string .= $k.'='.$v.'; ';
        }
        $string = trim($string);
        $headers->addTextHeader('DKIM-Signature', $string);
        // Add the last DKIM-Signature
        $tmp = $headers->getAll('DKIM-Signature');
        $this->_dkimHeader = end($tmp);
        $this->_addHeader(trim($this->_dkimHeader->toString())."\r\n b=", true);
        $this->_endOfHeaders();
        if ($this->_debugHeaders) {
            $headers->addTextHeader('X-DebugHash', base64_encode($this->_headerHash));
        }
        $this->_dkimHeader->setValue($string.' b='.trim(chunk_split(base64_encode($this->_getEncryptedHash()), 73, ' ')));

        return $this;
    }

    /* Private helpers */

    protected function _addHeader($header, $is_sig = false)
    {
        switch ($this->_headerCanon) {
            case 'relaxed':
                // Prepare Header and cascade
                $exploded = explode(':', $header, 2);
                $name = strtolower(trim($exploded[0]));
                $value = str_replace("\r\n", '', $exploded[1]);
                $value = preg_replace("/[ \t][ \t]+/", ' ', $value);
                $header = $name.':'.trim($value).($is_sig ? '' : "\r\n");
            case 'simple':
                // Nothing to do
        }
        $this->_addToHeaderHash($header);
    }

    
    protected function _endOfHeaders()
    {
    }

    protected function _canonicalizeBody($string)
    {
        $len = strlen($string);
        $canon = '';
        $method = ($this->_bodyCanon == 'relaxed');
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
                        if ($method) {
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
                        // todo handle it but should never happen
                    }
                    break;
                case ' ':
                case "\t":
                    if ($method) {
                        $this->_bodyCanonSpace = true;
                        break;
                    }
                default:
                    if ($this->_bodyCanonEmptyCounter > 0) {
                        $canon .= str_repeat("\r\n", $this->_bodyCanonEmptyCounter);
                        $this->_bodyCanonEmptyCounter = 0;
                    }
                    if ($this->_bodyCanonSpace) {
                        $this->_bodyCanonLine .= ' ';
                        $canon .= ' ';
                        $this->_bodyCanonSpace = false;
                    }
                    $this->_bodyCanonLine .= $string[$i];
                    $canon .= $string[$i];
            }
        }
        $this->_addToBodyHash($canon);
    }

    protected function _endOfBody()
    {
        // Add trailing Line return if last line is non empty
        if (strlen($this->_bodyCanonLine) > 0) {
            $this->_addToBodyHash("\r\n");
        }
        $this->_bodyHash = hash_final($this->_bodyHashHandler, true);
    }

    private function _addToBodyHash($string)
    {
        $len = strlen($string);
        if ($len > ($new_len = ($this->_maxLen - $this->_bodyLen))) {
            $string = substr($string, 0, $new_len);
            $len = $new_len;
        }
        hash_update($this->_bodyHashHandler, $string);
        $this->_bodyLen += $len;
    }

    private function _addToHeaderHash($header)
    {
        if ($this->_debugHeaders) {
            $this->_debugHeadersData[] = trim($header);
        }
        $this->_headerCanonData .= $header;
    }

    
    private function _getEncryptedHash()
    {
        $signature = '';
        switch ($this->_hashAlgorithm) {
            case 'rsa-sha1':
                $algorithm = OPENSSL_ALGO_SHA1;
                break;
            case 'rsa-sha256':
                $algorithm = OPENSSL_ALGO_SHA256;
                break;
        }
        $pkeyId = openssl_get_privatekey($this->_privateKey);
        if (!$pkeyId) {
            throw new Swift_SwiftException('Unable to load DKIM Private Key ['.openssl_error_string().']');
        }
        if (openssl_sign($this->_headerCanonData, $signature, $pkeyId, $algorithm)) {
            return $signature;
        }
        throw new Swift_SwiftException('Unable to sign DKIM Hash ['.openssl_error_string().']');
    }
}
