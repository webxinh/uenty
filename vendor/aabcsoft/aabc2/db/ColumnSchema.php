<?php


namespace aabc\db;

use aabc\base\Object;


class ColumnSchema extends Object
{
    
    public $name;
    
    public $allowNull;
    
    public $type;
    
    public $phpType;
    
    public $dbType;
    
    public $defaultValue;
    
    public $enumValues;
    
    public $size;
    
    public $precision;
    
    public $scale;
    
    public $isPrimaryKey;
    
    public $autoIncrement = false;
    
    public $unsigned;
    
    public $comment;


    
    public function phpTypecast($value)
    {
        return $this->typecast($value);
    }

    
    public function dbTypecast($value)
    {
        // the default implementation does the same as casting for PHP, but it should be possible
        // to override this with annotation of explicit PDO type.
        return $this->typecast($value);
    }

    
    protected function typecast($value)
    {
        if ($value === '' && $this->type !== Schema::TYPE_TEXT && $this->type !== Schema::TYPE_STRING && $this->type !== Schema::TYPE_BINARY && $this->type !== Schema::TYPE_CHAR) {
            return null;
        }
        if ($value === null || gettype($value) === $this->phpType || $value instanceof Expression || $value instanceof Query) {
            return $value;
        }
        switch ($this->phpType) {
            case 'resource':
            case 'string':
                if (is_resource($value)) {
                    return $value;
                }
                if (is_float($value)) {
                    // ensure type cast always has . as decimal separator in all locales
                    return str_replace(',', '.', (string) $value);
                }
                return (string) $value;
            case 'integer':
                return (int) $value;
            case 'boolean':
                // treating a 0 bit value as false too
                // https://github.com/aabcsoft/aabc2/issues/9006
                return (bool) $value && $value !== "\0";
            case 'double':
                return (double) $value;
        }

        return $value;
    }
}
