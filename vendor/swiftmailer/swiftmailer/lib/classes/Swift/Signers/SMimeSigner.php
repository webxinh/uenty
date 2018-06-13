<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Signers_SMimeSigner implements Swift_Signers_BodySigner
{
    protected $signCertificate;
    protected $signPrivateKey;
    protected $encryptCert;
    protected $signThenEncrypt = true;
    protected $signLevel;
    protected $encryptLevel;
    protected $signOptions;
    protected $encryptOptions;
    protected $encryptCipher;
    protected $extraCerts = null;

    
    protected $replacementFactory;

    
    protected $headerFactory;

    
    public function __construct($signCertificate = null, $signPrivateKey = null, $encryptCertificate = null)
    {
        if (null !== $signPrivateKey) {
            $this->setSignCertificate($signCertificate, $signPrivateKey);
        }

        if (null !== $encryptCertificate) {
            $this->setEncryptCertificate($encryptCertificate);
        }

        $this->replacementFactory = Swift_DependencyContainer::getInstance()
            ->lookup('transport.replacementfactory');

        $this->signOptions = PKCS7_DETACHED;

        // Supported since php5.4
        if (defined('OPENSSL_CIPHER_AES_128_CBC')) {
            $this->encryptCipher = OPENSSL_CIPHER_AES_128_CBC;
        } else {
            $this->encryptCipher = OPENSSL_CIPHER_RC2_128;
        }
    }

    
    public static function newInstance($certificate = null, $privateKey = null)
    {
        return new self($certificate, $privateKey);
    }

    
    public function setSignCertificate($certificate, $privateKey = null, $signOptions = PKCS7_DETACHED, $extraCerts = null)
    {
        $this->signCertificate = 'file://'.str_replace('\\', '/', realpath($certificate));

        if (null !== $privateKey) {
            if (is_array($privateKey)) {
                $this->signPrivateKey = $privateKey;
                $this->signPrivateKey[0] = 'file://'.str_replace('\\', '/', realpath($privateKey[0]));
            } else {
                $this->signPrivateKey = 'file://'.str_replace('\\', '/', realpath($privateKey));
            }
        }

        $this->signOptions = $signOptions;
        if (null !== $extraCerts) {
            $this->extraCerts = str_replace('\\', '/', realpath($extraCerts));
        }

        return $this;
    }

    
    public function setEncryptCertificate($recipientCerts, $cipher = null)
    {
        if (is_array($recipientCerts)) {
            $this->encryptCert = array();

            foreach ($recipientCerts as $cert) {
                $this->encryptCert[] = 'file://'.str_replace('\\', '/', realpath($cert));
            }
        } else {
            $this->encryptCert = 'file://'.str_replace('\\', '/', realpath($recipientCerts));
        }

        if (null !== $cipher) {
            $this->encryptCipher = $cipher;
        }

        return $this;
    }

    
    public function getSignCertificate()
    {
        return $this->signCertificate;
    }

    
    public function getSignPrivateKey()
    {
        return $this->signPrivateKey;
    }

    
    public function setSignThenEncrypt($signThenEncrypt = true)
    {
        $this->signThenEncrypt = $signThenEncrypt;

        return $this;
    }

    
    public function isSignThenEncrypt()
    {
        return $this->signThenEncrypt;
    }

    
    public function reset()
    {
        return $this;
    }

    
    public function signMessage(Swift_Message $message)
    {
        if (null === $this->signCertificate && null === $this->encryptCert) {
            return $this;
        }

        // Store the message using ByteStream to a file{1}
        // Remove all Children
        // Sign file{1}, parse the new MIME headers and set them on the primary MimeEntity
        // Set the singed-body as the new body (without boundary)

        $messageStream = new Swift_ByteStream_TemporaryFileByteStream();
        $this->toSMimeByteStream($messageStream, $message);
        $message->setEncoder(Swift_DependencyContainer::getInstance()->lookup('mime.rawcontentencoder'));

        $message->setChildren(array());
        $this->streamToMime($messageStream, $message);
    }

    
    public function getAlteredHeaders()
    {
        return array('Content-Type', 'Content-Transfer-Encoding', 'Content-Disposition');
    }

    
    protected function toSMimeByteStream(Swift_InputByteStream $inputStream, Swift_Message $message)
    {
        $mimeEntity = $this->createMessage($message);
        $messageStream = new Swift_ByteStream_TemporaryFileByteStream();

        $mimeEntity->toByteStream($messageStream);
        $messageStream->commit();

        if (null !== $this->signCertificate && null !== $this->encryptCert) {
            $temporaryStream = new Swift_ByteStream_TemporaryFileByteStream();

            if ($this->signThenEncrypt) {
                $this->messageStreamToSignedByteStream($messageStream, $temporaryStream);
                $this->messageStreamToEncryptedByteStream($temporaryStream, $inputStream);
            } else {
                $this->messageStreamToEncryptedByteStream($messageStream, $temporaryStream);
                $this->messageStreamToSignedByteStream($temporaryStream, $inputStream);
            }
        } elseif ($this->signCertificate !== null) {
            $this->messageStreamToSignedByteStream($messageStream, $inputStream);
        } else {
            $this->messageStreamToEncryptedByteStream($messageStream, $inputStream);
        }
    }

    
    protected function createMessage(Swift_Message $message)
    {
        $mimeEntity = new Swift_Message('', $message->getBody(), $message->getContentType(), $message->getCharset());
        $mimeEntity->setChildren($message->getChildren());

        $messageHeaders = $mimeEntity->getHeaders();
        $messageHeaders->remove('Message-ID');
        $messageHeaders->remove('Date');
        $messageHeaders->remove('Subject');
        $messageHeaders->remove('MIME-Version');
        $messageHeaders->remove('To');
        $messageHeaders->remove('From');

        return $mimeEntity;
    }

    
    protected function messageStreamToSignedByteStream(Swift_FileStream $outputStream, Swift_InputByteStream $inputStream)
    {
        $signedMessageStream = new Swift_ByteStream_TemporaryFileByteStream();

        $args = array($outputStream->getPath(), $signedMessageStream->getPath(), $this->signCertificate, $this->signPrivateKey, array(), $this->signOptions);
        if (null !== $this->extraCerts) {
            $args[] = $this->extraCerts;
        }

        if (!call_user_func_array('openssl_pkcs7_sign', $args)) {
            throw new Swift_IoException(sprintf('Failed to sign S/Mime message. Error: "%s".', openssl_error_string()));
        }

        $this->copyFromOpenSSLOutput($signedMessageStream, $inputStream);
    }

    
    protected function messageStreamToEncryptedByteStream(Swift_FileStream $outputStream, Swift_InputByteStream $is)
    {
        $encryptedMessageStream = new Swift_ByteStream_TemporaryFileByteStream();

        if (!openssl_pkcs7_encrypt($outputStream->getPath(), $encryptedMessageStream->getPath(), $this->encryptCert, array(), 0, $this->encryptCipher)) {
            throw new Swift_IoException(sprintf('Failed to encrypt S/Mime message. Error: "%s".', openssl_error_string()));
        }

        $this->copyFromOpenSSLOutput($encryptedMessageStream, $is);
    }

    
    protected function copyFromOpenSSLOutput(Swift_OutputByteStream $fromStream, Swift_InputByteStream $toStream)
    {
        $bufferLength = 4096;
        $filteredStream = new Swift_ByteStream_TemporaryFileByteStream();
        $filteredStream->addFilter($this->replacementFactory->createFilter("\r\n", "\n"), 'CRLF to LF');
        $filteredStream->addFilter($this->replacementFactory->createFilter("\n", "\r\n"), 'LF to CRLF');

        while (false !== ($buffer = $fromStream->read($bufferLength))) {
            $filteredStream->write($buffer);
        }

        $filteredStream->flushBuffers();

        while (false !== ($buffer = $filteredStream->read($bufferLength))) {
            $toStream->write($buffer);
        }

        $toStream->commit();
    }

    
    protected function streamToMime(Swift_OutputByteStream $fromStream, Swift_Message $message)
    {
        $bufferLength = 78;
        $headerData = '';

        $fromStream->setReadPointer(0);

        while (($buffer = $fromStream->read($bufferLength)) !== false) {
            $headerData .= $buffer;

            if (false !== strpos($buffer, "\r\n\r\n")) {
                break;
            }
        }

        $headersPosEnd = strpos($headerData, "\r\n\r\n");
        $headerData = trim($headerData);
        $headerData = substr($headerData, 0, $headersPosEnd);
        $headerLines = explode("\r\n", $headerData);
        unset($headerData);

        $headers = array();
        $currentHeaderName = '';

        foreach ($headerLines as $headerLine) {
            // Line separated
            if (ctype_space($headerLines[0]) || false === strpos($headerLine, ':')) {
                $headers[$currentHeaderName] .= ' '.trim($headerLine);
                continue;
            }

            $header = explode(':', $headerLine, 2);
            $currentHeaderName = strtolower($header[0]);
            $headers[$currentHeaderName] = trim($header[1]);
        }

        $messageStream = new Swift_ByteStream_TemporaryFileByteStream();
        $messageStream->addFilter($this->replacementFactory->createFilter("\r\n", "\n"), 'CRLF to LF');
        $messageStream->addFilter($this->replacementFactory->createFilter("\n", "\r\n"), 'LF to CRLF');

        $messageHeaders = $message->getHeaders();

        // No need to check for 'application/pkcs7-mime', as this is always base64
        if ('multipart/signed;' === substr($headers['content-type'], 0, 17)) {
            if (!preg_match('/boundary=("[^"]+"|(?:[^\s]+|$))/is', $headers['content-type'], $contentTypeData)) {
                throw new Swift_SwiftException('Failed to find Boundary parameter');
            }

            $boundary = trim($contentTypeData['1'], '"');

            // Skip the header and CRLF CRLF
            $fromStream->setReadPointer($headersPosEnd + 4);

            while (false !== ($buffer = $fromStream->read($bufferLength))) {
                $messageStream->write($buffer);
            }

            $messageStream->commit();

            $messageHeaders->remove('Content-Transfer-Encoding');
            $message->setContentType($headers['content-type']);
            $message->setBoundary($boundary);
            $message->setBody($messageStream);
        } else {
            $fromStream->setReadPointer($headersPosEnd + 4);

            if (null === $this->headerFactory) {
                $this->headerFactory = Swift_DependencyContainer::getInstance()->lookup('mime.headerfactory');
            }

            $message->setContentType($headers['content-type']);
            $messageHeaders->set($this->headerFactory->createTextHeader('Content-Transfer-Encoding', $headers['content-transfer-encoding']));
            $messageHeaders->set($this->headerFactory->createTextHeader('Content-Disposition', $headers['content-disposition']));

            while (false !== ($buffer = $fromStream->read($bufferLength))) {
                $messageStream->write($buffer);
            }

            $messageStream->commit();
            $message->setBody($messageStream);
        }
    }
}
