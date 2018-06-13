<?php


namespace aabc\db\sqlite;

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
                $format = '{type}{check}{append}';
                break;
            case self::CATEGORY_NUMERIC:
                $format = '{type}{length}{unsigned}{notnull}{unique}{check}{default}{append}';
                break;
            default:
                $format = '{type}{length}{notnull}{unique}{check}{default}{append}';
        }

        return $this->buildCompleteString($format);
    }
}
