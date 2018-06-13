<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Mime_SimpleMimeEntity implements Swift_Mime_MimeEntity
{
    
    private $_headers;

    
    private $_body;

    
    private $_encoder;

    
    private $_grammar;

    
    private $_boundary;

    
    private $_compositeRanges = array(
        'multipart/mixed' => array(self::LEVEL_TOP, self::LEVEL_MIXED),
        'multipart/alternative' => array(self::LEVEL_MIXED, self::LEVEL_ALTERNATIVE),
        'multipart/related' => array(self::LEVEL_ALTERNATIVE, self::LEVEL_RELATED),
    );

    
    private $_compoundLevelFilters = array();

    
    private $_nestingLevel = self::LEVEL_ALTERNATIVE;

    
    private $_cache;

    
    private $_immediateChildren = array();

    
    private $_children = array();

    
    private $_maxLineLength = 78;

    
    private $_alternativePartOrder = array(
        'text/plain' => 1,
        'text/html' => 2,
        'multipart/related' => 3,
    );

    
    private $_id;

    
    private $_cacheKey;

    protected $_userContentType;

    
    public function __construct(Swift_Mime_HeaderSet $headers, Swift_Mime_ContentEncoder $encoder, Swift_KeyCache $cache, Swift_Mime_Grammar $grammar)
    {
        $this->_cacheKey = md5(uniqid(getmypid().mt_rand(), true));
        $this->_cache = $cache;
        $this->_headers = $headers;
        $this->_grammar = $grammar;
        $this->setEncoder($encoder);
        $this->_headers->defineOrdering(array('Content-Type', 'Content-Transfer-Encoding'));

        // This array specifies that, when the entire MIME document contains
        // $compoundLevel, then for each child within $level, if its Content-Type
        // is $contentType then it should be treated as if it's level is
        // $neededLevel instead.  I tried to write that unambiguously! :-\
        // Data Structure:
        // array (
        //   $compoundLevel => array(
        //     $level => array(
        //       $contentType => $neededLevel
        //     )
        //   )
        // )

        $this->_compoundLevelFilters = array(
            (self::LEVEL_ALTERNATIVE + self::LEVEL_RELATED) => array(
                self::LEVEL_ALTERNATIVE => array(
                    'text/plain' => self::LEVEL_ALTERNATIVE,
                    'text/html' => self::LEVEL_RELATED,
                    ),
                ),
            );

        $this->_id = $this->getRandomId();
    }

    
    public function generateId()
    {
        $this->setId($this->getRandomId());

        return $this->_id;
    }

    
    public function getHeaders()
    {
        return $this->_headers;
    }

    
    public function getNestingLevel()
    {
        return $this->_nestingLevel;
    }

    
    public function getContentType()
    {
        return $this->_getHeaderFieldModel('Content-Type');
    }

    
    public function setContentType($type)
    {
        $this->_setContentTypeInHeaders($type);
        // Keep track of the value so that if the content-type changes automatically
        // due to added child entities, it can be restored if they are later removed
        $this->_userContentType = $type;

        return $this;
    }

    
    public function getId()
    {
        $tmp = (array) $this->_getHeaderFieldModel($this->_getIdField());

        return $this->_headers->has($this->_getIdField()) ? current($tmp) : $this->_id;
    }

    
    public function setId($id)
    {
        if (!$this->_setHeaderFieldModel($this->_getIdField(), $id)) {
            $this->_headers->addIdHeader($this->_getIdField(), $id);
        }
        $this->_id = $id;

        return $this;
    }

    
    public function getDescription()
    {
        return $this->_getHeaderFieldModel('Content-Description');
    }

    
    public function setDescription($description)
    {
        if (!$this->_setHeaderFieldModel('Content-Description', $description)) {
            $this->_headers->addTextHeader('Content-Description', $description);
        }

        return $this;
    }

    
    public function getMaxLineLength()
    {
        return $this->_maxLineLength;
    }

    
    public function setMaxLineLength($length)
    {
        $this->_maxLineLength = $length;

        return $this;
    }

    
    public function getChildren()
    {
        return $this->_children;
    }

    
    public function setChildren(array $children, $compoundLevel = null)
    {
        // TODO: Try to refactor this logic

        $compoundLevel = isset($compoundLevel) ? $compoundLevel : $this->_getCompoundLevel($children);
        $immediateChildren = array();
        $grandchildren = array();
        $newContentType = $this->_userContentType;

        foreach ($children as $child) {
            $level = $this->_getNeededChildLevel($child, $compoundLevel);
            if (empty($immediateChildren)) {
                //first iteration
                $immediateChildren = array($child);
            } else {
                $nextLevel = $this->_getNeededChildLevel($immediateChildren[0], $compoundLevel);
                if ($nextLevel == $level) {
                    $immediateChildren[] = $child;
                } elseif ($level < $nextLevel) {
                    // Re-assign immediateChildren to grandchildren
                    $grandchildren = array_merge($grandchildren, $immediateChildren);
                    // Set new children
                    $immediateChildren = array($child);
                } else {
                    $grandchildren[] = $child;
                }
            }
        }

        if ($immediateChildren) {
            $lowestLevel = $this->_getNeededChildLevel($immediateChildren[0], $compoundLevel);

            // Determine which composite media type is needed to accommodate the
            // immediate children
            foreach ($this->_compositeRanges as $mediaType => $range) {
                if ($lowestLevel > $range[0] && $lowestLevel <= $range[1]) {
                    $newContentType = $mediaType;

                    break;
                }
            }

            // Put any grandchildren in a subpart
            if (!empty($grandchildren)) {
                $subentity = $this->_createChild();
                $subentity->_setNestingLevel($lowestLevel);
                $subentity->setChildren($grandchildren, $compoundLevel);
                array_unshift($immediateChildren, $subentity);
            }
        }

        $this->_immediateChildren = $immediateChildren;
        $this->_children = $children;
        $this->_setContentTypeInHeaders($newContentType);
        $this->_fixHeaders();
        $this->_sortChildren();

        return $this;
    }

    
    public function getBody()
    {
        return $this->_body instanceof Swift_OutputByteStream ? $this->_readStream($this->_body) : $this->_body;
    }

    
    public function setBody($body, $contentType = null)
    {
        if ($body !== $this->_body) {
            $this->_clearCache();
        }

        $this->_body = $body;
        if (isset($contentType)) {
            $this->setContentType($contentType);
        }

        return $this;
    }

    
    public function getEncoder()
    {
        return $this->_encoder;
    }

    
    public function setEncoder(Swift_Mime_ContentEncoder $encoder)
    {
        if ($encoder !== $this->_encoder) {
            $this->_clearCache();
        }

        $this->_encoder = $encoder;
        $this->_setEncoding($encoder->getName());
        $this->_notifyEncoderChanged($encoder);

        return $this;
    }

    
    public function getBoundary()
    {
        if (!isset($this->_boundary)) {
            $this->_boundary = '_=_swift_v4_'.time().'_'.md5(getmypid().mt_rand().uniqid('', true)).'_=_';
        }

        return $this->_boundary;
    }

    
    public function setBoundary($boundary)
    {
        $this->_assertValidBoundary($boundary);
        $this->_boundary = $boundary;

        return $this;
    }

    
    public function charsetChanged($charset)
    {
        $this->_notifyCharsetChanged($charset);
    }

    
    public function encoderChanged(Swift_Mime_ContentEncoder $encoder)
    {
        $this->_notifyEncoderChanged($encoder);
    }

    
    public function toString()
    {
        $string = $this->_headers->toString();
        $string .= $this->_bodyToString();

        return $string;
    }

    
    protected function _bodyToString()
    {
        $string = '';

        if (isset($this->_body) && empty($this->_immediateChildren)) {
            if ($this->_cache->hasKey($this->_cacheKey, 'body')) {
                $body = $this->_cache->getString($this->_cacheKey, 'body');
            } else {
                $body = "\r\n".$this->_encoder->encodeString($this->getBody(), 0, $this->getMaxLineLength());
                $this->_cache->setString($this->_cacheKey, 'body', $body, Swift_KeyCache::MODE_WRITE);
            }
            $string .= $body;
        }

        if (!empty($this->_immediateChildren)) {
            foreach ($this->_immediateChildren as $child) {
                $string .= "\r\n\r\n--".$this->getBoundary()."\r\n";
                $string .= $child->toString();
            }
            $string .= "\r\n\r\n--".$this->getBoundary()."--\r\n";
        }

        return $string;
    }

    
    public function __toString()
    {
        return $this->toString();
    }

    
    public function toByteStream(Swift_InputByteStream $is)
    {
        $is->write($this->_headers->toString());
        $is->commit();

        $this->_bodyToByteStream($is);
    }

    
    protected function _bodyToByteStream(Swift_InputByteStream $is)
    {
        if (empty($this->_immediateChildren)) {
            if (isset($this->_body)) {
                if ($this->_cache->hasKey($this->_cacheKey, 'body')) {
                    $this->_cache->exportToByteStream($this->_cacheKey, 'body', $is);
                } else {
                    $cacheIs = $this->_cache->getInputByteStream($this->_cacheKey, 'body');
                    if ($cacheIs) {
                        $is->bind($cacheIs);
                    }

                    $is->write("\r\n");

                    if ($this->_body instanceof Swift_OutputByteStream) {
                        $this->_body->setReadPointer(0);

                        $this->_encoder->encodeByteStream($this->_body, $is, 0, $this->getMaxLineLength());
                    } else {
                        $is->write($this->_encoder->encodeString($this->getBody(), 0, $this->getMaxLineLength()));
                    }

                    if ($cacheIs) {
                        $is->unbind($cacheIs);
                    }
                }
            }
        }

        if (!empty($this->_immediateChildren)) {
            foreach ($this->_immediateChildren as $child) {
                $is->write("\r\n\r\n--".$this->getBoundary()."\r\n");
                $child->toByteStream($is);
            }
            $is->write("\r\n\r\n--".$this->getBoundary()."--\r\n");
        }
    }

    
    protected function _getIdField()
    {
        return 'Content-ID';
    }

    
    protected function _getHeaderFieldModel($field)
    {
        if ($this->_headers->has($field)) {
            return $this->_headers->get($field)->getFieldBodyModel();
        }
    }

    
    protected function _setHeaderFieldModel($field, $model)
    {
        if ($this->_headers->has($field)) {
            $this->_headers->get($field)->setFieldBodyModel($model);

            return true;
        }

        return false;
    }

    
    protected function _getHeaderParameter($field, $parameter)
    {
        if ($this->_headers->has($field)) {
            return $this->_headers->get($field)->getParameter($parameter);
        }
    }

    
    protected function _setHeaderParameter($field, $parameter, $value)
    {
        if ($this->_headers->has($field)) {
            $this->_headers->get($field)->setParameter($parameter, $value);

            return true;
        }

        return false;
    }

    
    protected function _fixHeaders()
    {
        if (count($this->_immediateChildren)) {
            $this->_setHeaderParameter('Content-Type', 'boundary',
                $this->getBoundary()
                );
            $this->_headers->remove('Content-Transfer-Encoding');
        } else {
            $this->_setHeaderParameter('Content-Type', 'boundary', null);
            $this->_setEncoding($this->_encoder->getName());
        }
    }

    
    protected function _getCache()
    {
        return $this->_cache;
    }

    
    protected function _getGrammar()
    {
        return $this->_grammar;
    }

    
    protected function _clearCache()
    {
        $this->_cache->clearKey($this->_cacheKey, 'body');
    }

    
    protected function getRandomId()
    {
        $idLeft = md5(getmypid().'.'.time().'.'.uniqid(mt_rand(), true));
        $idRight = !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'swift.generated';
        $id = $idLeft.'@'.$idRight;

        try {
            $this->_assertValidId($id);
        } catch (Swift_RfcComplianceException $e) {
            $id = $idLeft.'@swift.generated';
        }

        return $id;
    }

    private function _readStream(Swift_OutputByteStream $os)
    {
        $string = '';
        while (false !== $bytes = $os->read(8192)) {
            $string .= $bytes;
        }

        $os->setReadPointer(0);

        return $string;
    }

    private function _setEncoding($encoding)
    {
        if (!$this->_setHeaderFieldModel('Content-Transfer-Encoding', $encoding)) {
            $this->_headers->addTextHeader('Content-Transfer-Encoding', $encoding);
        }
    }

    private function _assertValidBoundary($boundary)
    {
        if (!preg_match('/^[a-z0-9\'\(\)\+_\-,\.\/:=\?\ ]{0,69}[a-z0-9\'\(\)\+_\-,\.\/:=\?]$/Di', $boundary)) {
            throw new Swift_RfcComplianceException('Mime boundary set is not RFC 2046 compliant.');
        }
    }

    private function _setContentTypeInHeaders($type)
    {
        if (!$this->_setHeaderFieldModel('Content-Type', $type)) {
            $this->_headers->addParameterizedHeader('Content-Type', $type);
        }
    }

    private function _setNestingLevel($level)
    {
        $this->_nestingLevel = $level;
    }

    private function _getCompoundLevel($children)
    {
        $level = 0;
        foreach ($children as $child) {
            $level |= $child->getNestingLevel();
        }

        return $level;
    }

    private function _getNeededChildLevel($child, $compoundLevel)
    {
        $filter = array();
        foreach ($this->_compoundLevelFilters as $bitmask => $rules) {
            if (($compoundLevel & $bitmask) === $bitmask) {
                $filter = $rules + $filter;
            }
        }

        $realLevel = $child->getNestingLevel();
        $lowercaseType = strtolower($child->getContentType());

        if (isset($filter[$realLevel]) && isset($filter[$realLevel][$lowercaseType])) {
            return $filter[$realLevel][$lowercaseType];
        }

        return $realLevel;
    }

    private function _createChild()
    {
        return new self($this->_headers->newInstance(), $this->_encoder, $this->_cache, $this->_grammar);
    }

    private function _notifyEncoderChanged(Swift_Mime_ContentEncoder $encoder)
    {
        foreach ($this->_immediateChildren as $child) {
            $child->encoderChanged($encoder);
        }
    }

    private function _notifyCharsetChanged($charset)
    {
        $this->_encoder->charsetChanged($charset);
        $this->_headers->charsetChanged($charset);
        foreach ($this->_immediateChildren as $child) {
            $child->charsetChanged($charset);
        }
    }

    private function _sortChildren()
    {
        $shouldSort = false;
        foreach ($this->_immediateChildren as $child) {
            // NOTE: This include alternative parts moved into a related part
            if ($child->getNestingLevel() == self::LEVEL_ALTERNATIVE) {
                $shouldSort = true;
                break;
            }
        }

        // Sort in order of preference, if there is one
        if ($shouldSort) {
            usort($this->_immediateChildren, array($this, '_childSortAlgorithm'));
        }
    }

    private function _childSortAlgorithm($a, $b)
    {
        $typePrefs = array();
        $types = array(strtolower($a->getContentType()), strtolower($b->getContentType()));

        foreach ($types as $type) {
            $typePrefs[] = array_key_exists($type, $this->_alternativePartOrder) ? $this->_alternativePartOrder[$type] : max($this->_alternativePartOrder) + 1;
        }

        return $typePrefs[0] >= $typePrefs[1] ? 1 : -1;
    }

    // -- Destructor

    
    public function __destruct()
    {
        $this->_cache->clearAll($this->_cacheKey);
    }

    
    private function _assertValidId($id)
    {
        if (!preg_match('/^'.$this->_grammar->getDefinition('id-left').'@'.$this->_grammar->getDefinition('id-right').'$/D', $id)) {
            throw new Swift_RfcComplianceException('Invalid ID given <'.$id.'>');
        }
    }

    
    public function __clone()
    {
        $this->_headers = clone $this->_headers;
        $this->_encoder = clone $this->_encoder;
        $this->_cacheKey = md5(uniqid(getmypid().mt_rand(), true));
        $children = array();
        foreach ($this->_children as $pos => $child) {
            $children[$pos] = clone $child;
        }
        $this->setChildren($children);
    }
}
