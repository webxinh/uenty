<?php


namespace aabc\db;

use Aabc;
use aabc\base\Object;


class ColumnSchemaBuilder extends Object
{
    // Internally used constants representing categories that abstract column types fall under.
    // See [[$categoryMap]] for mappings of abstract column types to category.
    // @since 2.0.8
    const CATEGORY_PK = 'pk';
    const CATEGORY_STRING = 'string';
    const CATEGORY_NUMERIC = 'numeric';
    const CATEGORY_TIME = 'time';
    const CATEGORY_OTHER = 'other';

    
    protected $type;
    
    protected $length;
    
    protected $isNotNull;
    
    protected $isUnique = false;
    
    protected $check;
    
    protected $default;
    
    protected $append;
    
    protected $isUnsigned = false;
    
    protected $after;
    
    protected $isFirst;


    
    public $categoryMap = [
        Schema::TYPE_PK => self::CATEGORY_PK,
        Schema::TYPE_UPK => self::CATEGORY_PK,
        Schema::TYPE_BIGPK => self::CATEGORY_PK,
        Schema::TYPE_UBIGPK => self::CATEGORY_PK,
        Schema::TYPE_CHAR => self::CATEGORY_STRING,
        Schema::TYPE_STRING => self::CATEGORY_STRING,
        Schema::TYPE_TEXT => self::CATEGORY_STRING,
        Schema::TYPE_SMALLINT => self::CATEGORY_NUMERIC,
        Schema::TYPE_INTEGER => self::CATEGORY_NUMERIC,
        Schema::TYPE_BIGINT => self::CATEGORY_NUMERIC,
        Schema::TYPE_FLOAT => self::CATEGORY_NUMERIC,
        Schema::TYPE_DOUBLE => self::CATEGORY_NUMERIC,
        Schema::TYPE_DECIMAL => self::CATEGORY_NUMERIC,
        Schema::TYPE_DATETIME => self::CATEGORY_TIME,
        Schema::TYPE_TIMESTAMP => self::CATEGORY_TIME,
        Schema::TYPE_TIME => self::CATEGORY_TIME,
        Schema::TYPE_DATE => self::CATEGORY_TIME,
        Schema::TYPE_BINARY => self::CATEGORY_OTHER,
        Schema::TYPE_BOOLEAN => self::CATEGORY_NUMERIC,
        Schema::TYPE_MONEY => self::CATEGORY_NUMERIC,
    ];
    
    public $db;
    
    public $comment;

    
    public function __construct($type, $length = null, $db = null, $config = [])
    {
        $this->type = $type;
        $this->length = $length;
        $this->db = $db;
        parent::__construct($config);
    }

    
    public function notNull()
    {
        $this->isNotNull = true;
        return $this;
    }

    
    public function null()
    {
        $this->isNotNull = false;
        return $this;
    }

    
    public function unique()
    {
        $this->isUnique = true;
        return $this;
    }

    
    public function check($check)
    {
        $this->check = $check;
        return $this;
    }

    
    public function defaultValue($default)
    {
        if ($default === null) {
            $this->null();
        }

        $this->default = $default;
        return $this;
    }

    
    public function comment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    
    public function unsigned()
    {
        switch ($this->type) {
            case Schema::TYPE_PK:
                $this->type = Schema::TYPE_UPK;
                break;
            case Schema::TYPE_BIGPK:
                $this->type = Schema::TYPE_UBIGPK;
                break;
        }
        $this->isUnsigned = true;
        return $this;
    }

    
    public function after($after)
    {
        $this->after = $after;
        return $this;
    }

    
    public function first()
    {
        $this->isFirst = true;
        return $this;
    }

    
    public function defaultExpression($default)
    {
        $this->default = new Expression($default);
        return $this;
    }

    
    public function append($sql)
    {
        $this->append = $sql;
        return $this;
    }

    
    public function __toString()
    {
        switch ($this->getTypeCategory()) {
            case self::CATEGORY_PK:
                $format = '{type}{check}{comment}{append}';
                break;
            default:
                $format = '{type}{length}{notnull}{unique}{default}{check}{comment}{append}';
        }
        return $this->buildCompleteString($format);
    }

    
    protected function buildLengthString()
    {
        if ($this->length === null || $this->length === []) {
            return '';
        }
        if (is_array($this->length)) {
            $this->length = implode(',', $this->length);
        }
        return "({$this->length})";
    }

    
    protected function buildNotNullString()
    {
        if ($this->isNotNull === true) {
            return ' NOT NULL';
        } elseif ($this->isNotNull === false) {
            return ' NULL';
        } else {
            return '';
        }
    }

    
    protected function buildUniqueString()
    {
        return $this->isUnique ? ' UNIQUE' : '';
    }

    
    protected function buildDefaultString()
    {
        if ($this->default === null) {
            return $this->isNotNull === false ? ' DEFAULT NULL' : '';
        }

        $string = ' DEFAULT ';
        switch (gettype($this->default)) {
            case 'integer':
                $string .= (string) $this->default;
                break;
            case 'double':
                // ensure type cast always has . as decimal separator in all locales
                $string .= str_replace(',', '.', (string) $this->default);
                break;
            case 'boolean':
                $string .= $this->default ? 'TRUE' : 'FALSE';
                break;
            case 'object':
                $string .= (string) $this->default;
                break;
            default:
                $string .= "'{$this->default}'";
        }

        return $string;
    }

    
    protected function buildCheckString()
    {
        return $this->check !== null ? " CHECK ({$this->check})" : '';
    }

    
    protected function buildUnsignedString()
    {
        return '';
    }

    
    protected function buildAfterString()
    {
        return '';
    }

    
    protected function buildFirstString()
    {
        return '';
    }

    
    protected function buildAppendString()
    {
        return $this->append !== null ? ' ' . $this->append : '';
    }

    
    protected function getTypeCategory()
    {
        return isset($this->categoryMap[$this->type]) ? $this->categoryMap[$this->type] : null;
    }

    
    protected function buildCommentString()
    {
        return '';
    }

    
    protected function buildCompleteString($format)
    {
        $placeholderValues = [
            '{type}' => $this->type,
            '{length}' => $this->buildLengthString(),
            '{unsigned}' => $this->buildUnsignedString(),
            '{notnull}' => $this->buildNotNullString(),
            '{unique}' => $this->buildUniqueString(),
            '{default}' => $this->buildDefaultString(),
            '{check}' => $this->buildCheckString(),
            '{comment}' => $this->buildCommentString(),
            '{pos}' => $this->isFirst ? $this->buildFirstString() : $this->buildAfterString(),
            '{append}' => $this->buildAppendString(),
        ];
        return strtr($format, $placeholderValues);
    }
}
