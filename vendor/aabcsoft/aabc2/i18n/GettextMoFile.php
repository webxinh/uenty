<?php


namespace aabc\i18n;

use aabc\base\Exception;


class GettextMoFile extends GettextFile
{
    
    public $useBigEndian = false;


    
    public function load($filePath, $context)
    {
        if (false === ($fileHandle = @fopen($filePath, 'rb'))) {
            throw new Exception('Unable to read file "' . $filePath . '".');
        }
        if (false === @flock($fileHandle, LOCK_SH)) {
            throw new Exception('Unable to lock file "' . $filePath . '" for reading.');
        }

        // magic
        $array = unpack('c', $this->readBytes($fileHandle, 4));
        $magic = current($array);
        if ($magic == -34) {
            $this->useBigEndian = false;
        } elseif ($magic == -107) {
            $this->useBigEndian = true;
        } else {
            throw new Exception('Invalid MO file: ' . $filePath . ' (magic: ' . $magic . ').');
        }

        // revision
        $revision = $this->readInteger($fileHandle);
        if ($revision !== 0) {
            throw new Exception('Invalid MO file revision: ' . $revision . '.');
        }

        $count = $this->readInteger($fileHandle);
        $sourceOffset = $this->readInteger($fileHandle);
        $targetOffset = $this->readInteger($fileHandle);

        $sourceLengths = [];
        $sourceOffsets = [];
        fseek($fileHandle, $sourceOffset);
        for ($i = 0; $i < $count; ++$i) {
            $sourceLengths[] = $this->readInteger($fileHandle);
            $sourceOffsets[] = $this->readInteger($fileHandle);
        }

        $targetLengths = [];
        $targetOffsets = [];
        fseek($fileHandle, $targetOffset);
        for ($i = 0; $i < $count; ++$i) {
            $targetLengths[] = $this->readInteger($fileHandle);
            $targetOffsets[] = $this->readInteger($fileHandle);
        }

        $messages = [];
        for ($i = 0; $i < $count; ++$i) {
            $id = $this->readString($fileHandle, $sourceLengths[$i], $sourceOffsets[$i]);
            $separatorPosition = strpos($id, chr(4));


            if ((!$context && $separatorPosition === false) || ($context && $separatorPosition !== false && strncmp($id, $context, $separatorPosition) === 0)) {
                if ($separatorPosition !== false) {
                    $id = substr($id, $separatorPosition+1);
                }

                $message = $this->readString($fileHandle, $targetLengths[$i], $targetOffsets[$i]);
                $messages[$id] = $message;
            }
        }

        @flock($fileHandle, LOCK_UN);
        @fclose($fileHandle);

        return $messages;
    }

    
    public function save($filePath, $messages)
    {
        if (false === ($fileHandle = @fopen($filePath, 'wb'))) {
            throw new Exception('Unable to write file "' . $filePath . '".');
        }
        if (false === @flock($fileHandle, LOCK_EX)) {
            throw new Exception('Unable to lock file "' . $filePath . '" for reading.');
        }

        // magic
        if ($this->useBigEndian) {
            $this->writeBytes($fileHandle, pack('c*', 0x95, 0x04, 0x12, 0xde)); // -107
        } else {
            $this->writeBytes($fileHandle, pack('c*', 0xde, 0x12, 0x04, 0x95)); // -34
        }

        // revision
        $this->writeInteger($fileHandle, 0);

        // message count
        $messageCount = count($messages);
        $this->writeInteger($fileHandle, $messageCount);

        // offset of source message table
        $offset = 28;
        $this->writeInteger($fileHandle, $offset);
        $offset += $messageCount * 8;
        $this->writeInteger($fileHandle, $offset);

        // hashtable size, omitted
        $this->writeInteger($fileHandle, 0);
        $offset += $messageCount * 8;
        $this->writeInteger($fileHandle, $offset);

        // length and offsets for source messages
        foreach (array_keys($messages) as $id) {
            $length = strlen($id);
            $this->writeInteger($fileHandle, $length);
            $this->writeInteger($fileHandle, $offset);
            $offset += $length + 1;
        }

        // length and offsets for target messages
        foreach ($messages as $message) {
            $length = strlen($message);
            $this->writeInteger($fileHandle, $length);
            $this->writeInteger($fileHandle, $offset);
            $offset += $length + 1;
        }

        // source messages
        foreach (array_keys($messages) as $id) {
            $this->writeString($fileHandle, $id);
        }

        // target messages
        foreach ($messages as $message) {
            $this->writeString($fileHandle, $message);
        }

        @flock($fileHandle, LOCK_UN);
        @fclose($fileHandle);
    }

    
    protected function readBytes($fileHandle, $byteCount = 1)
    {
        if ($byteCount > 0) {
            return fread($fileHandle, $byteCount);
        } else {
            return null;
        }
    }

    
    protected function writeBytes($fileHandle, $bytes)
    {
        return fwrite($fileHandle, $bytes);
    }

    
    protected function readInteger($fileHandle)
    {
        $array = unpack($this->useBigEndian ? 'N' : 'V', $this->readBytes($fileHandle, 4));

        return current($array);
    }

    
    protected function writeInteger($fileHandle, $integer)
    {
        return $this->writeBytes($fileHandle, pack($this->useBigEndian ? 'N' : 'V', (int) $integer));
    }

    
    protected function readString($fileHandle, $length, $offset = null)
    {
        if ($offset !== null) {
            fseek($fileHandle, $offset);
        }

        return $this->readBytes($fileHandle, $length);
    }

    
    protected function writeString($fileHandle, $string)
    {
        return $this->writeBytes($fileHandle, $string. "\0");
    }
}
