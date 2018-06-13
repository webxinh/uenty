<?php


namespace aabc\db\oci;

use aabc\db\ColumnSchemaBuilder as AbstractColumnSchemaBuilder;


class ColumnSchemaBuilder extends AbstractColumnSchemaBuilder
{
    
    protected function buildUnsignedString()
    {
        return $this->isUnsigned ? ' UNSIGNED' : '';
    }

    
    public function __toString()
    {
        switch ($this->getTypeCategory()) {
            case self::CATEGORY_PK:
                $format = '{type}{length}{check}{append}';
                break;
            case self::CATEGORY_NUMERIC:
                $format = '{type}{length}{unsigned}{default}{notnull}{check}{append}';
                break;
            default:
                $format = '{type}{length}{default}{notnull}{check}{append}';
        }
        return $this->buildCompleteString($format);
    }
}
