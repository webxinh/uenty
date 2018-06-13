<?php


namespace aabc\db;


trait SchemaBuilderTrait
{
    
    protected abstract function getDb();

    
    public function primaryKey($length = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_PK, $length);
    }

    
    public function bigPrimaryKey($length = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_BIGPK, $length);
    }

    
    public function char($length = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_CHAR, $length);
    }

    
    public function string($length = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_STRING, $length);
    }

    
    public function text()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_TEXT);
    }

    
    public function smallInteger($length = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_SMALLINT, $length);
    }

    
    public function integer($length = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_INTEGER, $length);
    }

    
    public function bigInteger($length = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_BIGINT, $length);
    }

    
    public function float($precision = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_FLOAT, $precision);
    }

    
    public function double($precision = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_DOUBLE, $precision);
    }

    
    public function decimal($precision = null, $scale = null)
    {
        $length = [];
        if ($precision !== null) {
            $length[] = $precision;
        }
        if ($scale !== null) {
            $length[] = $scale;
        }
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_DECIMAL, $length);
    }

    
    public function dateTime($precision = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_DATETIME, $precision);
    }

    
    public function timestamp($precision = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_TIMESTAMP, $precision);
    }

    
    public function time($precision = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_TIME, $precision);
    }

    
    public function date()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_DATE);
    }

    
    public function binary($length = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_BINARY, $length);
    }

    
    public function boolean()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_BOOLEAN);
    }

    
    public function money($precision = null, $scale = null)
    {
        $length = [];
        if ($precision !== null) {
            $length[] = $precision;
        }
        if ($scale !== null) {
            $length[] = $scale;
        }
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_MONEY, $length);
    }
}
