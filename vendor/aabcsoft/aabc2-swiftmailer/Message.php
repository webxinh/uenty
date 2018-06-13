<?php


namespace aabc\swiftmailer;

use Aabc;
use aabc\base\InvalidConfigException;
use aabc\helpers\ArrayHelper;
use aabc\mail\BaseMessage;


class Message extends BaseMessage
{
    
    private $_swiftMessage;
    
    private $signers = [];


    
    public function getSwiftMessage()
    {
        if (!is_object($this->_swiftMessage)) {
            $this->_swiftMessage = $this->createSwiftMessage();
        }

        return $this->_swiftMessage;
    }

    
    public function getCharset()
    {
        return $this->getSwiftMessage()->getCharset();
    }

    
    public function setCharset($charset)
    {
        $this->getSwiftMessage()->setCharset($charset);

        return $this;
    }

    
    public function getFrom()
    {
        return $this->getSwiftMessage()->getFrom();
    }

    
    public function setFrom($from)
    {
        $this->getSwiftMessage()->setFrom($from);

        return $this;
    }

    
    public function getReplyTo()
    {
        return $this->getSwiftMessage()->getReplyTo();
    }

    
    public function setReplyTo($replyTo)
    {
        $this->getSwiftMessage()->setReplyTo($replyTo);

        return $this;
    }

    
    public function getTo()
    {
        return $this->getSwiftMessage()->getTo();
    }

    
    public function setTo($to)
    {
        $this->getSwiftMessage()->setTo($to);

        return $this;
    }

    
    public function getCc()
    {
        return $this->getSwiftMessage()->getCc();
    }

    
    public function setCc($cc)
    {
        $this->getSwiftMessage()->setCc($cc);

        return $this;
    }

    
    public function getBcc()
    {
        return $this->getSwiftMessage()->getBcc();
    }

    
    public function setBcc($bcc)
    {
        $this->getSwiftMessage()->setBcc($bcc);

        return $this;
    }

    
    public function getSubject()
    {
        return $this->getSwiftMessage()->getSubject();
    }

    
    public function setSubject($subject)
    {
        $this->getSwiftMessage()->setSubject($subject);

        return $this;
    }

    
    public function setTextBody($text)
    {
        $this->setBody($text, 'text/plain');

        return $this;
    }

    
    public function setHtmlBody($html)
    {
        $this->setBody($html, 'text/html');

        return $this;
    }

    
    protected function setBody($body, $contentType)
    {
        $message = $this->getSwiftMessage();
        $oldBody = $message->getBody();
        $charset = $message->getCharset();
        if (empty($oldBody)) {
            $parts = $message->getChildren();
            $partFound = false;
            foreach ($parts as $key => $part) {
                if (!($part instanceof \Swift_Mime_Attachment)) {
                    /* @var $part \Swift_Mime_MimePart */
                    if ($part->getContentType() == $contentType) {
                        $charset = $part->getCharset();
                        unset($parts[$key]);
                        $partFound = true;
                        break;
                    }
                }
            }
            if ($partFound) {
                reset($parts);
                $message->setChildren($parts);
                $message->addPart($body, $contentType, $charset);
            } else {
                $message->setBody($body, $contentType);
            }
        } else {
            $oldContentType = $message->getContentType();
            if ($oldContentType == $contentType) {
                $message->setBody($body, $contentType);
            } else {
                $message->setBody(null);
                $message->setContentType(null);
                $message->addPart($oldBody, $oldContentType, $charset);
                $message->addPart($body, $contentType, $charset);
            }
        }
    }

    
    public function attach($fileName, array $options = [])
    {
        $attachment = \Swift_Attachment::fromPath($fileName);
        if (!empty($options['fileName'])) {
            $attachment->setFilename($options['fileName']);
        }
        if (!empty($options['contentType'])) {
            $attachment->setContentType($options['contentType']);
        }
        $this->getSwiftMessage()->attach($attachment);

        return $this;
    }

    
    public function attachContent($content, array $options = [])
    {
        $attachment = \Swift_Attachment::newInstance($content);
        if (!empty($options['fileName'])) {
            $attachment->setFilename($options['fileName']);
        }
        if (!empty($options['contentType'])) {
            $attachment->setContentType($options['contentType']);
        }
        $this->getSwiftMessage()->attach($attachment);

        return $this;
    }

    
    public function embed($fileName, array $options = [])
    {
        $embedFile = \Swift_EmbeddedFile::fromPath($fileName);
        if (!empty($options['fileName'])) {
            $embedFile->setFilename($options['fileName']);
        }
        if (!empty($options['contentType'])) {
            $embedFile->setContentType($options['contentType']);
        }

        return $this->getSwiftMessage()->embed($embedFile);
    }

    
    public function embedContent($content, array $options = [])
    {
        $embedFile = \Swift_EmbeddedFile::newInstance($content);
        if (!empty($options['fileName'])) {
            $embedFile->setFilename($options['fileName']);
        }
        if (!empty($options['contentType'])) {
            $embedFile->setContentType($options['contentType']);
        }

        return $this->getSwiftMessage()->embed($embedFile);
    }

    
    public function setSignature($signature)
    {
        if (!empty($this->signers)) {
            // clear previously set signers
            $swiftMessage = $this->getSwiftMessage();
            foreach ($this->signers as $signer) {
                $swiftMessage->detachSigner($signer);
            }
            $this->signers = [];
        }
        return $this->addSignature($signature);
    }

    
    public function addSignature($signature)
    {
        if ($signature instanceof \Swift_Signer) {
            $signer = $signature;
        } elseif (is_callable($signature)) {
            $signer = call_user_func($signature);
        } elseif (is_array($signature)) {
            $signer = $this->createSwiftSigner($signature);
        } else {
            throw new InvalidConfigException('Signature should be instance of "Swift_Signer", callable or array configuration');
        }

        $this->getSwiftMessage()->attachSigner($signer);
        $this->signers[] = $signer;

        return $this;
    }

    
    protected function createSwiftSigner($signature)
    {
        if (!isset($signature['type'])) {
            throw new InvalidConfigException('Signature configuration should contain "type" key');
        }
        switch (strtolower($signature['type'])) {
            case 'dkim' :
                $domain = ArrayHelper::getValue($signature, 'domain', null);
                $selector = ArrayHelper::getValue($signature, 'selector', null);
                if (isset($signature['key'])) {
                    $privateKey = $signature['key'];
                } elseif (isset($signature['file'])) {
                    $privateKey = file_get_contents(Aabc::getAlias($signature['file']));
                } else {
                    throw new InvalidConfigException("Either 'key' or 'file' signature option should be specified");
                }
                return new \Swift_Signers_DKIMSigner($privateKey, $domain, $selector);
            case 'opendkim' :
                $domain = ArrayHelper::getValue($signature, 'domain', null);
                $selector = ArrayHelper::getValue($signature, 'selector', null);
                if (isset($signature['key'])) {
                    $privateKey = $signature['key'];
                } elseif (isset($signature['file'])) {
                    $privateKey = file_get_contents(Aabc::getAlias($signature['file']));
                } else {
                    throw new InvalidConfigException("Either 'key' or 'file' signature option should be specified");
                }
                return new \Swift_Signers_OpenDKIMSigner($privateKey, $domain, $selector);
            default:
                throw new InvalidConfigException("Unrecognized signature type '{$signature['type']}'");
        }
    }

    
    public function toString()
    {
        return $this->getSwiftMessage()->toString();
    }

    
    protected function createSwiftMessage()
    {
        return new \Swift_Message();
    }

    // Headers setup :

    
    public function addHeader($name, $value)
    {
        $this->getSwiftMessage()->getHeaders()->addTextHeader($name, $value);
        return $this;
    }

    
    public function setHeader($name, $value)
    {
        $headerSet = $this->getSwiftMessage()->getHeaders();

        if ($headerSet->has($name)) {
            $headerSet->remove($name);
        }

        foreach ((array)$value as $v) {
            $headerSet->addTextHeader($name, $v);
        }

        return $this;
    }

    
    public function getHeader($name)
    {
        $headerSet = $this->getSwiftMessage()->getHeaders();
        if (!$headerSet->has($name)) {
            return [];
        }

        $headers = [];
        foreach ($headerSet->getAll($name) as $header) {
            $headers[] = $header->getValue();
        }
        return $headers;
    }

    // SwiftMessage shortcuts :

    
    public function setReturnPath($address)
    {
        $this->getSwiftMessage()->setReturnPath($address);
        return $this;
    }

    
    public function getReturnPath()
    {
        return $this->getSwiftMessage()->getReturnPath();
    }

    
    public function setPriority($priority)
    {
        $this->getSwiftMessage()->setPriority($priority);
        return $this;
    }

    
    public function getPriority()
    {
        return $this->getSwiftMessage()->getPriority();
    }

    
    public function setReadReceiptTo($addresses)
    {
        $this->getSwiftMessage()->setReadReceiptTo($addresses);
        return $this;
    }

    
    public function getReadReceiptTo()
    {
        return $this->getSwiftMessage()->getReadReceiptTo();
    }
}