<?php


namespace aabc\db\cubrid;

use aabc\db\ColumnSchemaBuilder as AbstractColumnSchemaBuilder;


class ColumnSchemaBuilder extends AbstractColumnSchemaBuilder
{
    
    protected function buildUnsignedString()
    {
        return $this->isUnsigned ? ' UNSIGNED' : '';
    }

    
    protected function buildAfterString()
    {
        return $this->after !== null ?
            ' AFTER ' . $this->db->quoteColumnName($this->after) :
            '';
    }

    
    protected function buildFirstString()
    {
        return $this->isFirst ? ' FIRST' : '';
    }

    
    protected function buildCommentString()
    {
        return $this->comment !== null ? ' COMMENT ' . $this->db->quoteValue($this->comment) : '';
    }

    
    public function __toString()
    {
        switch ($this->getTypeCategory()) {
            case self::CATEGORY_PK:
                $format = '{type}{check}{comment}{append}{pos}';
                break;
            case self::CATEGORY_NUMERIC:
                $format = '{type}{length}{unsigned}{notnull}{unique}{default}{check}{comment}{append}{pos}';
                break;
            default:
                $format = '{type}{length}{notnull}{unique}{default}{check}{comment}{append}{pos}';
        }
        return $this->buildCompleteString($format);
    }
}
